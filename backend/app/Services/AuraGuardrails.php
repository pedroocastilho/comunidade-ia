<?php

namespace App\Services;

use App\Models\User;

/**
 * Validador das adaptacoes propostas pela Aura (PRD secao 6).
 * A IA so pode trocar atividade de dia FUTURO por um equivalente do pool
 * do template. Qualquer proposta fora disso e rejeitada.
 */
class AuraGuardrails
{
    public function validarAdaptacao(User $user, int $dia, string $tipo, int|string $substitutoId): bool
    {
        $jornada = $user->jornadaAtiva;
        if (! $jornada || ! in_array($tipo, ['ritual', 'aula', 'acao'], true)) {
            return false;
        }

        // Nunca dias passados nem o dia corrente ja iniciado; nunca fora da jornada.
        if ($dia <= $jornada->dia_atual || $dia > $jornada->template->duracao_dias) {
            return false;
        }

        $pool = $this->poolEquivalentes($jornada, $dia, $tipo);

        if ($tipo === 'acao') {
            // Para acao, o substituto e o indice (0-based) no pool de textos.
            return is_numeric($substitutoId) && array_key_exists((int) $substitutoId, $pool);
        }

        return in_array((int) $substitutoId, array_map('intval', $pool), true);
    }

    /**
     * Aplica uma adaptacao JA VALIDADA na copia do usuario, com log auditavel.
     */
    public function aplicarAdaptacao(User $user, int $dia, string $tipo, int|string $substitutoId, string $motivo): void
    {
        $jornada = $user->jornadaAtiva;
        $jornadaDia = $jornada->dias()->where('dia', $dia)->first();
        $pool = $this->poolEquivalentes($jornada, $dia, $tipo);

        [$coluna, $anterior, $novo] = match ($tipo) {
            'ritual' => ['ritual_audio_id', $jornadaDia->ritual_audio_id, (int) $substitutoId],
            'aula' => ['aula_id', $jornadaDia->aula_id, (int) $substitutoId],
            'acao' => ['acao_texto', $jornadaDia->acao_texto, $pool[(int) $substitutoId]],
        };

        $jornadaDia->update([
            $coluna => $novo,
            'adaptado_por_ia' => true,
            'origem_adaptacao' => [
                'gatilho' => 'chat',
                'motivo' => $motivo,
                'atividade' => $tipo,
                'anterior' => $anterior,
                'novo' => $novo,
                'em' => now()->toIso8601String(),
            ],
        ]);
    }

    private function poolEquivalentes($jornada, int $dia, string $tipo): array
    {
        $templateDia = $jornada->template->dias()->where('dia', $dia)->first();

        return $templateDia?->equivalentes[$tipo] ?? [];
    }
}
