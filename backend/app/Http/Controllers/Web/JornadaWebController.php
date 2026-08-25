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

        return Inertia::render('App/Home', [
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
            'checkin_hoje' => $user->checkins()->whereDate('created_at', today())->first()?->only(['humor', 'texto']),
            'progresso_semana' => collect(range(1, 7))->map(fn ($d) => $checkinsSemana->contains($d))->all(),
            'apelido' => $user->apelido ?? $user->name,
        ]);
    }

    public function concluirAtividade(Request $request)
    {
        $request->validate(['tipo' => 'required|in:ritual,acao']);

        $user = $request->user();
        $jornada = $user->jornadaAtiva;
        $dia = $jornada ? $this->jornadas->diaAtual($jornada) : null;

        if ($dia) {
            $this->jornadas->concluirAtividade($dia, $request->input('tipo'));
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
