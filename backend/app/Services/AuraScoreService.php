<?php

namespace App\Services;

use App\Models\AuraScore;
use App\Models\Dimensao;
use App\Models\User;

/**
 * Formula deterministica do Aura Score (PRD secao 4).
 * REGRA DE OURO: o LLM nunca participa deste calculo.
 */
class AuraScoreService
{
    /** Ordem fixa das dimensoes — usada como desempate final do ponto de atencao. */
    public const SLUGS = ['prosperidade', 'relacionamentos', 'saude-energia', 'proposito', 'mentalidade'];

    /** Mapeia pergunta de escala -> dimensao. p8 e p9 compoem mentalidade juntas. */
    private const PERGUNTA_DIMENSAO = [
        'p4' => 'prosperidade',
        'p5' => 'relacionamentos',
        'p6' => 'saude-energia',
        'p7' => 'proposito',
    ];

    private const PESO_PRINCIPAL = 2.0;

    private const PESO_SECUNDARIO = 1.5;

    private const PESO_PADRAO = 1.0;

    private const MAX_PADROES = 2;

    /**
     * @param  array  $escalas  ['p4' => 0-10, ..., 'p9' => 0-10]
     * @return array{score_global: int, scores_dimensoes: array<string,int>, dimensao_prioritaria: string, ponto_atencao: string, padroes: string[]}
     */
    public function calcular(array $escalas, string $objetivoPrincipal, ?string $objetivoSecundario): array
    {
        $scores = [];
        foreach (self::PERGUNTA_DIMENSAO as $pergunta => $slug) {
            $scores[$slug] = $escalas[$pergunta] * 10;
        }

        // Mentalidade = media entre estado da mente (p8) e crenca (p9).
        $scores['mentalidade'] = (int) round(($escalas['p8'] * 10 + $escalas['p9'] * 10) / 2);

        $somaPonderada = 0.0;
        $somaPesos = 0.0;
        foreach ($scores as $slug => $score) {
            $peso = match ($slug) {
                $objetivoPrincipal => self::PESO_PRINCIPAL,
                $objetivoSecundario => self::PESO_SECUNDARIO,
                default => self::PESO_PADRAO,
            };
            $somaPonderada += $score * $peso;
            $somaPesos += $peso;
        }

        $scoreGlobal = (int) round($somaPonderada / $somaPesos);

        return [
            'score_global' => $scoreGlobal,
            'scores_dimensoes' => $scores,
            'dimensao_prioritaria' => $objetivoPrincipal,
            'ponto_atencao' => $this->pontoAtencao($scores, $objetivoPrincipal, $objetivoSecundario),
            'padroes' => $this->padroes($scoreGlobal, $scores, $escalas['p9']),
        ];
    }

    /**
     * Menor score; empate resolvido por objetivo principal, depois secundario,
     * depois ordem fixa das dimensoes.
     */
    private function pontoAtencao(array $scores, string $objetivoPrincipal, ?string $objetivoSecundario): string
    {
        $menor = min($scores);
        $empatadas = array_keys(array_filter($scores, fn ($s) => $s === $menor));

        foreach ([$objetivoPrincipal, $objetivoSecundario] as $objetivo) {
            if ($objetivo !== null && in_array($objetivo, $empatadas, true)) {
                return $objetivo;
            }
        }

        foreach (self::SLUGS as $slug) {
            if (in_array($slug, $empatadas, true)) {
                return $slug;
            }
        }

        return $empatadas[0];
    }

    /**
     * Regras avaliadas em ordem, acumulando no maximo 2.
     * base_solida so aparece sozinha, quando nada mais dispara.
     *
     * @return string[]
     */
    private function padroes(int $scoreGlobal, array $scores, int $crenca): array
    {
        $padroes = [];

        if ($scoreGlobal < 40) {
            $padroes[] = 'reconstrucao';
        }

        if ((max($scores) - min($scores)) > 30) {
            $padroes[] = 'desequilibrio';
        }

        if ($crenca <= 4) {
            $padroes[] = 'bloqueio_crenca';
        }

        if ($scoreGlobal >= 70 && $crenca >= 7) {
            $padroes[] = 'pronto_para_acelerar';
        }

        if ($padroes === []) {
            return ['base_solida'];
        }

        return array_slice($padroes, 0, self::MAX_PADROES);
    }

    /**
     * Persiste o resultado de calcular() para o usuario.
     */
    public function salvar(User $user, array $resultado): AuraScore
    {
        return AuraScore::create([
            'user_id' => $user->id,
            'score_global' => $resultado['score_global'],
            'scores_dimensoes' => $resultado['scores_dimensoes'],
            'dimensao_prioritaria_id' => Dimensao::where('slug', $resultado['dimensao_prioritaria'])->value('id'),
            'ponto_atencao' => $resultado['ponto_atencao'],
            'padroes' => $resultado['padroes'],
            'calculado_em' => now(),
        ]);
    }
}
