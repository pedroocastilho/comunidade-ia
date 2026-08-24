<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\Aviso;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\ProgressoAula;
use App\Services\BunnyService;
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

        return Inertia::render('App/Cursos', [
            'cursos' => $q->orderBy('ordem')->get(['id', 'titulo', 'slug', 'capa_url', 'descricao']),
            'categorias' => Categoria::orderBy('ordem')->get(['nome', 'slug']),
            'filtros' => $request->only(['busca', 'categoria']),
        ]);
    }

    public function curso(Request $request, string $slug)
    {
        $curso = Curso::where('status', 'publicado')->where('slug', $slug)
            ->with(['instrutor', 'modulos.aulas'])->firstOrFail();

        $concluidas = ProgressoAula::where('user_id', $request->user()->id)
            ->where('concluida', true)
            ->whereIn('aula_id', $curso->modulos->flatMap->aulas->pluck('id'))
            ->pluck('aula_id')->all();

        return Inertia::render('App/Curso', [
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

    public function aula(Request $request, Aula $aula, BunnyService $bunny)
    {
        $aula->load('modulo.curso.modulos.aulas');
        $curso = $aula->modulo->curso;

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

    public function concluirAula(Request $request, Aula $aula)
    {
        ProgressoAula::updateOrCreate(
            ['user_id' => $request->user()->id, 'aula_id' => $aula->id],
            ['concluida' => true],
        );

        return back();
    }
}
