<?php

namespace App\Services;

use App\Models\EventoAnalytics;
use App\Models\User;
use InvalidArgumentException;

/**
 * Registro server-side de eventos de produto (PRD secao 13).
 */
class AnalyticsService
{
    /** Nomes validos de eventos. Fora desta lista: erro em dev/teste, ignorado em producao. */
    public const EVENTOS = [
        'onboarding_started',
        'onboarding_completed',
        'journey_created',
        'journey_day_completed',
        'journey_adapted',
        'daily_plan_opened',
        'lesson_started',
        'lesson_completed',
        'aura_chat_started',
        'daily_checkin_completed',
        'premium_viewed',
        'premium_purchased',
        'subscription_blocked_view',
    ];

    public function registrar(string $nome, ?User $user = null, array $propriedades = []): void
    {
        if (! in_array($nome, self::EVENTOS, true)) {
            if (app()->environment(['local', 'testing'])) {
                throw new InvalidArgumentException("Evento de analytics desconhecido: {$nome}");
            }

            return; // em producao, evento invalido nao pode derrubar o fluxo
        }

        EventoAnalytics::create([
            'user_id' => $user?->id,
            'nome' => $nome,
            'propriedades' => $propriedades === [] ? null : $propriedades,
            'created_at' => now(),
        ]);
    }
}
