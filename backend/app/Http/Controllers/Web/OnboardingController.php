<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dimensao;
use App\Models\EventoAnalytics;
use App\Models\QuestionarioPergunta;
use App\Models\QuestionarioResposta;
use App\Services\AnalyticsService;
use App\Services\AuraScoreService;
use App\Services\JornadaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function questionario(Request $request, AnalyticsService $analytics)
    {
        $user = $request->user();

        if ($user->onboarding_completo_em) {
            return redirect()->route('home');
        }

        $jaIniciou = EventoAnalytics::where('nome', 'onboarding_started')
            ->where('user_id', $user->id)->exists();
        if (! $jaIniciou) {
            $analytics->registrar('onboarding_started', $user);
        }

        return Inertia::render('App/Onboarding', [
            'perguntas' => QuestionarioPergunta::orderBy('ordem')
                ->get(['id', 'ordem', 'tipo', 'texto', 'opcoes', 'obrigatoria']),
        ]);
    }

    public function salvar(
        Request $request,
        AuraScoreService $scoreService,
        JornadaService $jornadaService,
        AnalyticsService $analytics,
    ) {
        $user = $request->user();

        if ($user->onboarding_completo_em) {
            return redirect()->route('home');
        }

        $perguntas = QuestionarioPergunta::orderBy('ordem')->get();

        // Validacao dinamica a partir do banco (a estrutura de pontuacao e fixa em codigo).
        $regras = [];
        foreach ($perguntas as $pergunta) {
            $chave = "respostas.{$pergunta->id}";
            $regra = [$pergunta->obrigatoria ? 'required' : 'nullable'];

            $regra = array_merge($regra, match ($pergunta->tipo) {
                'escala' => ['integer', 'between:0,10'],
                'escolha_unica' => [Rule::in(array_column($pergunta->opcoes ?? [], 'valor'))],
                default => ['string', 'max:1000'],
            });

            $regras[$chave] = $regra;
        }
        $request->validate($regras);

        $respostas = $request->input('respostas', []);
        $porOrdem = $perguntas->keyBy('ordem');
        $valor = fn (int $ordem) => $respostas[(string) $porOrdem[$ordem]->id]
            ?? $respostas[$porOrdem[$ordem]->id]
            ?? null;

        DB::transaction(function () use ($user, $perguntas, $respostas, $porOrdem, $valor, $scoreService, $jornadaService, $analytics) {
            foreach ($perguntas as $pergunta) {
                $resposta = $respostas[(string) $pergunta->id] ?? $respostas[$pergunta->id] ?? null;
                if ($resposta === null || $resposta === '') {
                    continue;
                }

                QuestionarioResposta::updateOrCreate(
                    ['user_id' => $user->id, 'pergunta_id' => $pergunta->id],
                    ['valor' => (string) $resposta],
                );

                if ($pergunta->alimenta_memoria) {
                    $rotulo = $resposta;
                    if ($pergunta->tipo === 'escolha_unica') {
                        $rotulo = collect($pergunta->opcoes)->firstWhere('valor', $resposta)['rotulo'] ?? $resposta;
                    }

                    $user->auraMemorias()->updateOrCreate(
                        ['origem' => 'questionario', 'tipo' => $this->tipoMemoria($pergunta->ordem), 'conteudo' => "{$pergunta->texto} {$rotulo}"],
                        ['ativo' => true],
                    );
                }
            }

            $user->update([
                'apelido' => $valor(1),
                'objetivo_principal' => $valor(2),
                'objetivo_secundario' => $valor(3),
                'tempo_disponivel' => $valor(11),
                'onboarding_completo_em' => now(),
            ]);

            $escalas = [];
            foreach ([4, 5, 6, 7, 8, 9] as $ordem) {
                $escalas['p'.$ordem] = (int) $valor($ordem);
            }

            $resultado = $scoreService->calcular($escalas, $valor(2), $valor(3));
            $scoreService->salvar($user, $resultado);

            $analytics->registrar('onboarding_completed', $user, [
                'objetivo' => $valor(2),
                'score_global' => $resultado['score_global'],
            ]);

            $jornada = $jornadaService->criarParaUsuario($user->fresh());
            if ($jornada) {
                $analytics->registrar('journey_created', $user, ['template' => $jornada->template_id]);
            }
        });

        return redirect()->route('aura-score');
    }

    public function score(Request $request)
    {
        $historico = $request->user()->auraScores()->orderByDesc('calculado_em')->limit(2)->get();
        $score = $historico->first();

        if (! $score) {
            return redirect()->route('onboarding');
        }

        return Inertia::render('App/AuraScore', [
            'score' => [
                'score_global' => $score->score_global,
                'scores_dimensoes' => $score->scores_dimensoes,
                'ponto_atencao' => $score->ponto_atencao,
                'padroes' => $score->padroes,
                'prioritaria' => $score->dimensaoPrioritaria?->slug,
            ],
            'dimensoes' => Dimensao::orderBy('ordem')->get(['nome', 'slug']),
            'apelido' => $request->user()->apelido ?? $request->user()->name,
            'tem_jornada' => $request->user()->jornadaAtiva !== null,
            'score_anterior' => $historico->count() > 1 ? $historico[1]->score_global : null,
        ]);
    }

    /**
     * Tipo de memoria por ordem da pergunta (PRD secao 3).
     */
    private function tipoMemoria(int $ordem): string
    {
        return match ($ordem) {
            1 => 'preferencia',
            2 => 'objetivo',
            default => 'contexto',
        };
    }
}
