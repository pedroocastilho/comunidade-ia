<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EventoAnalytics;
use App\Services\AnalyticsService;
use App\Services\JornadaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Home diaria e acoes da jornada (PRD secoes 6 e 7).
 */
class JornadaWebController extends Controller
{
    public function __construct(
        private JornadaService $jornadas,
        private AnalyticsService $analytics,
    ) {}

    public function home(Request $request)
    {
        $user = $request->user();
        $jornada = $user->jornadaAtiva?->load('template');
        $dia = $jornada ? $this->jornadas->diaAtual($jornada) : null;

        if ($jornada) {
            $jaAbriuHoje = EventoAnalytics::where('nome', 'daily_plan_opened')
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->exists();
            if (! $jaAbriuHoje) {
                $this->analytics->registrar('daily_plan_opened', $user, ['dia' => $jornada->dia_atual]);
            }
        }

        // Semana atual (seg..dom): dias com check-in.
        $inicioSemana = now()->startOfWeek();
        $checkinsSemana = $user->checkins()
            ->whereBetween('created_at', [$inicioSemana, $inicioSemana->copy()->endOfWeek()])
            ->get()
            ->map(fn ($c) => $c->created_at->dayOfWeekIso)
            ->unique();

        // Vitrine de conteudo (estrutura MeuFluxo): continue de onde parou + em alta + audios.
        $aulaIds = \App\Models\ProgressoAula::where('user_id', $user->id)
            ->where('concluida', false)->where('posicao_segundos', '>', 0)
            ->latest('updated_at')->pluck('aula_id');
        $cursoIds = \App\Models\Aula::whereIn('aulas.id', $aulaIds)
            ->join('modulos', 'aulas.modulo_id', '=', 'modulos.id')
            ->pluck('modulos.curso_id')->unique()->values();
        $continuar = \App\Models\Curso::whereIn('id', $cursoIds)
            ->where('status', 'publicado')
            ->limit(8)->get(['id', 'titulo', 'slug', 'capa_url', 'banner_url']);

        $emAlta = \App\Models\Curso::where('status', 'publicado')
            ->with('instrutor:id,nome')
            ->orderByDesc('destaque')->orderByDesc('views')->orderBy('ordem')
            ->limit(8)
            ->get(['id', 'titulo', 'slug', 'descricao', 'capa_url', 'banner_url', 'instrutor_id'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'titulo' => $c->titulo,
                'slug' => $c->slug,
                'descricao' => $c->descricao,
                'capa_url' => $c->banner_url ?? $c->capa_url,
                'instrutor' => $c->instrutor?->nome,
            ]);

        $audiosDestaque = \App\Models\Audio::where('status', 'publicado')
            ->orderBy('ordem')->latest('id')->limit(8)
            ->get(['id', 'tipo', 'titulo', 'capa_url', 'duracao']);

        // Curso em destaque para o hero (banner grande, estilo MeuFluxo)
        $destaque = \App\Models\Curso::where('status', 'publicado')->where('destaque', true)
            ->with('instrutor:id,nome')->first();

        return Inertia::render('App/Home', [
            // Sem jornada ativa: oferece a escolha da proxima (celebrando se concluiu uma)
            'jornada_concluida' => ! $jornada && $user->jornadas()->where('status', 'concluida')->exists(),
            'objetivos' => $jornada ? [] : \App\Models\Dimensao::orderBy('ordem')->get(['nome', 'slug']),
            'jornada' => $jornada ? [
                'dia' => $jornada->dia_atual,
                'total_dias' => $jornada->template->duracao_dias,
                'etapa' => $dia?->etapa,
                'status' => $jornada->status,
            ] : null,
            'atividades' => $dia ? [
                'ritual' => $dia->ritualAudio ? [
                    'id' => $dia->ritualAudio->id,
                    'titulo' => $dia->ritualAudio->titulo,
                    'duracao' => $dia->ritualAudio->duracao,
                    'concluido' => $dia->ritual_concluido,
                ] : null,
                'aula' => $dia->aula ? [
                    'id' => $dia->aula->id,
                    'titulo' => $dia->aula->titulo,
                    'duracao' => $dia->aula->duracao,
                    'concluida' => $dia->aula_concluida,
                ] : null,
                'acao' => $dia->acao_texto ? [
                    'texto' => $dia->acao_texto,
                    'concluida' => $dia->acao_concluida,
                ] : null,
            ] : null,
            'aura_score' => $user->auraScores()->latest('calculado_em')->value('score_global'),
            'streak' => app(\App\Services\GamificacaoService::class)->streak($user),
            'checkin_hoje' => $user->checkins()->whereDate('created_at', today())->first()?->only(['humor', 'texto']),
            'progresso_semana' => collect(range(1, 7))->map(fn ($d) => $checkinsSemana->contains($d))->all(),
            'apelido' => $user->apelido ?? $user->name,
            // Calendario de metas: as 3 em andamento mais proximas do prazo
            'metas_proximas' => $user->metas()->emAndamento()->orderBy('prazo')->orderBy('id')->limit(3)->get()
                ->map(fn ($meta) => MetaController::formatar($meta))->values(),
            'continuar' => $continuar,
            'em_alta' => $emAlta,
            'audios_destaque' => $audiosDestaque,
            // Video do hero (se existir em public/); senao o front usa a cena em canvas
            'hero_video' => file_exists(public_path('hero-aura.mp4')) ? '/hero-aura.mp4' : null,
            'destaque' => $destaque ? [
                'titulo' => $destaque->titulo,
                'slug' => $destaque->slug,
                'descricao' => \Illuminate\Support\Str::limit($destaque->descricao, 140),
                'banner_url' => $destaque->banner_url ?? $destaque->capa_url,
                'instrutor' => $destaque->instrutor?->nome,
            ] : null,
        ]);
    }

    /**
     * Inicia uma nova jornada (apos concluir a anterior, ou se nunca houve uma).
     */
    public function novaJornada(Request $request)
    {
        $request->validate([
            'objetivo' => 'required|string|exists:dimensoes,slug',
        ]);

        $user = $request->user();

        if ($user->jornadaAtiva) {
            return back(); // ja tem jornada em andamento
        }

        $user->update(['objetivo_principal' => $request->input('objetivo')]);

        $jornada = $this->jornadas->criarParaUsuario($user->fresh());
        if ($jornada) {
            $this->analytics->registrar('journey_created', $user, ['template' => $jornada->template_id]);
        }

        return redirect()->route('home');
    }

    public function concluirAtividade(Request $request)
    {
        $request->validate(['tipo' => 'required|in:ritual,acao']);

        $user = $request->user();
        $jornada = $user->jornadaAtiva;
        $dia = $jornada ? $this->jornadas->diaAtual($jornada) : null;

        if ($dia) {
            $tipo = $request->input('tipo');
            $primeiraVez = ! $dia->{$tipo === 'ritual' ? 'ritual_concluido' : 'acao_concluida'};

            $this->jornadas->concluirAtividade($dia, $tipo);

            if ($primeiraVez) {
                app(\App\Services\GamificacaoService::class)->conceder($user, $tipo);
            }

            $this->avancar($user, $jornada->fresh());
        }

        return back();
    }

    public function checkin(Request $request)
    {
        $request->validate([
            'humor' => 'required|integer|between:1,5',
            'texto' => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        $this->jornadas->registrarCheckin($user, (int) $request->input('humor'), $request->input('texto'));
        $this->analytics->registrar('daily_checkin_completed', $user, ['humor' => (int) $request->input('humor')]);

        if ($jornada = $user->jornadaAtiva) {
            $this->avancar($user, $jornada);
        }

        return back();
    }

    public function jornada(Request $request)
    {
        $user = $request->user();
        $jornada = $user->jornadaAtiva?->load(['template.dimensao', 'dias.ritualAudio', 'dias.aula']);

        return Inertia::render('App/Jornada', [
            'titulo' => $jornada?->template->titulo,
            'dias' => $jornada ? $jornada->dias->map(fn ($d) => [
                'dia' => $d->dia,
                'etapa' => $d->etapa,
                'concluido' => $d->concluido_em !== null,
                'atual' => $d->dia === $jornada->dia_atual && $jornada->status === 'ativa',
                'ritual' => $d->ritualAudio?->titulo,
                'aula' => $d->aula?->titulo,
                'acao' => $d->acao_texto,
            ])->values() : [],
        ]);
    }

    /**
     * Tenta avancar o dia e registra o evento de dia concluido.
     */
    private function avancar($user, $jornada): void
    {
        if ($this->jornadas->avancarSeCompleto($jornada)) {
            $this->analytics->registrar('journey_day_completed', $user, ['dia' => $jornada->dia_atual]);
        }
    }
}
