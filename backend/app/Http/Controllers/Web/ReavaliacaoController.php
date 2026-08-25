<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\QuestionarioPergunta;
use App\Services\AnalyticsService;
use App\Services\AuraScoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Remedicao do Aura Score ao fim de uma jornada (PRD secao 4 — evolucao).
 * Reaplica as 6 perguntas de escala; a formula e a mesma do onboarding.
 * Disponivel apenas sem jornada ativa (fim de ciclo), para o numero
 * refletir pratica real — nunca uso do app.
 */
class ReavaliacaoController extends Controller
{
    public function formulario(Request $request)
    {
        if ($request->user()->jornadaAtiva) {
            return redirect()->route('home');
        }

        return Inertia::render('App/Reavaliacao', [
            'perguntas' => QuestionarioPergunta::whereBetween('ordem', [4, 9])
                ->orderBy('ordem')
                ->get(['id', 'ordem', 'texto']),
        ]);
    }

    public function salvar(Request $request, AuraScoreService $scoreService, AnalyticsService $analytics)
    {
        if ($request->user()->jornadaAtiva) {
            return redirect()->route('home');
        }

        $request->validate([
            'escalas' => 'required|array',
            'escalas.p4' => 'required|integer|between:0,10',
            'escalas.p5' => 'required|integer|between:0,10',
            'escalas.p6' => 'required|integer|between:0,10',
            'escalas.p7' => 'required|integer|between:0,10',
            'escalas.p8' => 'required|integer|between:0,10',
            'escalas.p9' => 'required|integer|between:0,10',
        ]);

        $user = $request->user();
        $escalas = array_map('intval', $request->input('escalas'));

        $resultado = $scoreService->calcular($escalas, $user->objetivo_principal, $user->objetivo_secundario);
        $scoreService->salvar($user, $resultado);

        $analytics->registrar('onboarding_completed', $user, [
            'tipo' => 'reavaliacao',
            'score_global' => $resultado['score_global'],
        ]);

        return redirect()->route('aura-score');
    }
}
