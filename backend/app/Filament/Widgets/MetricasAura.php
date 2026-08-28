<?php

namespace App\Filament\Widgets;

use App\Models\AuraConversa;
use App\Models\Checkin;
use App\Models\EventoAnalytics;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Metricas principais do Circulo Aura no dashboard do admin (PRD secao 12).
 */
class MetricasAura extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $ativosHoje = EventoAnalytics::where('nome', 'daily_plan_opened')
            ->whereDate('created_at', today())
            ->distinct('user_id')
            ->count('user_id');

        return [
            Stat::make('Assinantes com acesso', User::where('tem_acesso', true)->where('perfil_ficticio', false)->count()),
            Stat::make('Onboarding completo', User::whereNotNull('onboarding_completo_em')->where('perfil_ficticio', false)->count()),
            Stat::make('Ativos hoje', $ativosHoje)
                ->description('abriram o plano do dia'),
            Stat::make('Check-ins hoje', Checkin::whereDate('created_at', today())->count()),
            Stat::make('Conversas com a Aura hoje', AuraConversa::whereDate('updated_at', today())->count()),
            Stat::make('Conversas de crise (7 dias)', AuraConversa::where('classificacao', 'crise')
                ->where('updated_at', '>=', now()->subDays(7))->count())
                ->description('exigem atenção do time')
                ->color('danger'),
        ];
    }
}
