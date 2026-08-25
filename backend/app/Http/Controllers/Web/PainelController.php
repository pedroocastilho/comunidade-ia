<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\Aviso;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\ProgressoAula;
use App\Services\AnalyticsService;
use App\Services\BunnyService;
use App\Services\JornadaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PainelController extends Controller
{
    public function home(Request $request)
    {
        $destaque = Curso::where('status', 'publicado')->where('destaque', true)
            ->with('instrutor')->first();

        // Continue de onde parou: cursos com aula em progresso nao concluida.
        $aulaIds = ProgressoAula::where('user_id', $request->user()->id)
            ->where('concluida', false)->where('posicao_segundos', '>', 0)
            ->latest('updated_at')->pluck('aula_id');
        $cursoIds = Aula::whereIn('aulas.id', $aulaIds)
            ->join('modulos', 'aulas.modulo_id', '=', 'modulos.id')
            ->pluck('modulos.curso_id')->unique()->values();
        $continuar = Curso::whereIn('id', $cursoIds)
            ->get(['id', 'titulo', 'slug', 'capa_url', 'banner_url']);

        $trilhas = Categoria::orderBy('ordem')->get()->map(fn ($cat) => [
            'nome' => $cat->nome,
            'slug' => $cat->slug,
            'cursos' => Curso::where('status', 'publicado')->where('categoria_id', $cat->id)
                ->orderBy('ordem')->get(['id', 'titulo', 'slug', 'capa_url', 'descricao']),
        ])->filter(fn ($t) => $t['cursos']->isNotEmpty())->values();

        $avisos = Aviso::whereNotNull('publicado_em')->where('publicado_em', '<=', now())
            ->latest('publicado_em')->limit(5)->get(['id', 'titulo', 'corpo', 'publicado_em']);

        return Inertia::render('App/Home', compact('destaque', 'continuar', 'trilhas', 'avisos'));
    }

    public function cursos(Request $request)
    {
        $q = Curso::where('status', 'publicado')->with('instrutor');

        if ($request->filled('busca')) {
            $q->where('titulo', 'like', '%'.$request->query('busca').'%');
        }
        if ($request->filled('categoria')) {
            $q->whereHas('categoria', fn ($c) => $c->where('slug', $request->query('categoria')));
        }

        $user = $request->user();

        return Inertia::render('App/Cursos', [
            'cursos' => $q->orderBy('ordem')
                ->get(['id', 'titulo', 'slug', 'capa_url', 'descricao', 'premium', 'produto_externo_id'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'titulo' => $c->titulo,
                    'slug' => $c->slug,
                    'capa_url' => $c->capa_url,
                    'descricao' => $c->descricao,
                    'bloqueado' => $c->premium && ! $user->comprou($c->produto_externo_id),
                ]),
            'categorias' => Categoria::orderBy('ordem')->get(['nome', 'slug']),
            'filtros' => $request->only(['busca', 'categoria']),
        ]);
    }

    public function curso(Request $request, string $slug, AnalyticsService $analytics)
    {
        $curso = Curso::where('status', 'publicado')->where('slug', $slug)
            ->with(['instrutor', 'modulos.aulas'])->firstOrFail();

        // Conteudo premium avulso: sem compra, vira pagina de venda (PRD secao 9)
        $bloqueado = $curso->premium && ! $request->user()->comprou($curso->produto_externo_id);
        if ($bloqueado) {
            $analytics->registrar('premium_viewed', $request->user(), ['tipo' => 'curso', 'id' => $curso->id]);
        }

        $concluidas = ProgressoAula::where('user_id', $request->user()->id)
            ->where('concluida', true)
            ->whereIn('aula_id', $curso->modulos->flatMap->aulas->pluck('id'))
            ->pluck('aula_id')->all();

        return Inertia::render('App/Curso', [
            'premium_bloqueado' => $bloqueado,
            'checkout_url' => $bloqueado ? $curso->checkout_url : null,
            'curso' => [
                'titulo' => $curso->titulo,
                'slug' => $curso->slug,
                'descricao' => $curso->descricao,
                'banner_url' => $curso->banner_url,
                'instrutor' => $curso->instrutor?->only(['nome', 'bio']),
                'modulos' => $curso->modulos->map(fn ($m) => [
                    'titulo' => $m->titulo,
                    'aulas' => $m->aulas->map(fn ($a) => [
                        'id' => $a->id,
                        'titulo' => $a->titulo,
                        'duracao' => $a->duracao,
                        'concluida' => in_array($a->id, $concluidas, true),
                    ]),
                ]),
            ],
        ]);
    }

    public function aula(Request $request, Aula $aula, BunnyService $bunny, AnalyticsService $analytics)
    {
        $aula->load('modulo.curso.modulos.aulas');
        $curso = $aula->modulo->curso;

        // Aula de curso premium sem compra: volta para a pagina de venda do curso
        if ($curso->premium && ! $request->user()->comprou($curso->produto_externo_id)) {
            return redirect()->route('curso', $curso->slug);
        }

        $analytics->registrar('lesson_started', $request->user(), ['tipo' => 'video', 'id' => $aula->id]);

        $progresso = ProgressoAula::where('user_id', $request->user()->id)
            ->where('aula_id', $aula->id)->first();

        // Progresso do usuario em todas as aulas do curso (para a playlist).
        $todasAulas = $curso->modulos->flatMap->aulas;
        $concluidas = ProgressoAula::where('user_id', $request->user()->id)
            ->where('concluida', true)
            ->whereIn('aula_id', $todasAulas->pluck('id'))
            ->pluck('aula_id')->all();

        // Sequencia linear para anterior/proxima.
        $sequencia = $todasAulas->pluck('id')->values();
        $pos = $sequencia->search($aula->id);
        $anterior = $pos > 0 ? $sequencia[$pos - 1] : null;
        $proxima = $pos < $sequencia->count() - 1 ? $sequencia[$pos + 1] : null;

        return Inertia::render('App/Player', [
            'aula' => [
                'id' => $aula->id,
                'titulo' => $aula->titulo,
                'descricao' => $aula->descricao,
                'material_url' => $aula->material_url,
                'concluida' => (bool) ($progresso->concluida ?? false),
                'video_embed_url' => $aula->bunny_video_id ? $bunny->embedUrl($aula->bunny_video_id) : null,
                'anterior_id' => $anterior,
                'proxima_id' => $proxima,
            ],
            'curso' => [
                'titulo' => $curso->titulo,
                'slug' => $curso->slug,
                'instrutor' => $curso->instrutor?->nome,
                'modulos' => $curso->modulos->map(fn ($m) => [
                    'titulo' => $m->titulo,
                    'aulas' => $m->aulas->map(fn ($a) => [
                        'id' => $a->id,
                        'titulo' => $a->titulo,
                        'concluida' => in_array($a->id, $concluidas, true),
                    ]),
                ]),
            ],
        ]);
    }

    public function concluirAula(Request $request, Aula $aula, JornadaService $jornadas, AnalyticsService $analytics)
    {
        $user = $request->user();

        $jaConcluida = ProgressoAula::where('user_id', $user->id)
            ->where('aula_id', $aula->id)->where('concluida', true)->exists();

        ProgressoAula::updateOrCreate(
            ['user_id' => $user->id, 'aula_id' => $aula->id],
            ['concluida' => true],
        );

        if (! $jaConcluida) {
            app(\App\Services\GamificacaoService::class)->conceder($user, 'aula');
        }

        $analytics->registrar('lesson_completed', $user, ['tipo' => 'video', 'id' => $aula->id]);

        // Ponte com a jornada: se for a aula do dia, marca a atividade e tenta avancar.
        $jornadas->concluirAulaDaJornada($user, $aula->id);
        if (($jornada = $user->jornadaAtiva) && $jornadas->avancarSeCompleto($jornada)) {
            $analytics->registrar('journey_day_completed', $user, ['dia' => $jornada->dia_atual]);
        }

        return back();
    }
}
