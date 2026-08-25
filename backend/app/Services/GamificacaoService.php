<?php

namespace App\Services;

use App\Models\Conquista;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * XP, niveis, streak e conquistas (PRD secao 11).
 * REGRA: nada aqui toca o Aura Score — engajamento e medida de vida
 * sao coisas separadas de proposito.
 */
class GamificacaoService
{
    /** XP por acao do usuario (fonte unica: conceder e chamado nos endpoints). */
    public const XP = [
        'ritual' => 10,
        'aula' => 15,
        'acao' => 10,
        'audio' => 10,
        'checkin' => 5,
        'dia_completo' => 20,
        'jornada_completa' => 200,
        'remedicao' => 50,
    ];

    /**
     * Soma XP da acao e desbloqueia conquistas pendentes.
     *
     * @return string[] slugs de conquistas recem-desbloqueadas
     */
    public function conceder(User $user, string $acao): array
    {
        if (isset(self::XP[$acao])) {
            $user->increment('xp', self::XP[$acao]);
        }

        return $this->verificarConquistas($user->fresh());
    }

    /** nivel = floor(sqrt(xp/100)) + 1  (100xp -> 2, 400 -> 3, 900 -> 4...) */
    public function nivel(int $xp): int
    {
        return (int) floor(sqrt($xp / 100)) + 1;
    }

    /** Progresso dentro do nivel atual, para a barra de XP. */
    public function progressoNivel(int $xp): array
    {
        $nivel = $this->nivel($xp);
        $base = (($nivel - 1) ** 2) * 100;      // xp minimo do nivel atual
        $proximo = ($nivel ** 2) * 100;          // xp para o proximo nivel

        return [
            'nivel' => $nivel,
            'xp' => $xp,
            'proximo_em' => $proximo,
            'percentual' => (int) round((($xp - $base) / max(1, $proximo - $base)) * 100),
        ];
    }

    /**
     * Dias-calendario consecutivos com check-in, terminando hoje ou ontem.
     */
    public function streak(User $user): int
    {
        $dias = $user->checkins()
            ->where('created_at', '>=', now()->subDays(60))
            ->get()
            ->map(fn ($c) => $c->created_at->toDateString())
            ->unique()
            ->sortDesc()
            ->values();

        if ($dias->isEmpty()) {
            return 0;
        }

        $inicio = Carbon::today();
        if ($dias[0] !== $inicio->toDateString()) {
            $inicio = $inicio->subDay(); // sequencia pode terminar ontem
            if ($dias[0] !== $inicio->toDateString()) {
                return 0;
            }
        }

        $streak = 0;
        $esperado = $inicio;
        foreach ($dias as $dia) {
            if ($dia !== $esperado->toDateString()) {
                break;
            }
            $streak++;
            $esperado = $esperado->copy()->subDay();
        }

        return $streak;
    }

    /**
     * Desbloqueia conquistas cujas condicoes ja foram atingidas.
     *
     * @return string[] slugs recem-desbloqueados
     */
    public function verificarConquistas(User $user): array
    {
        $conquistadas = $user->conquistas()->pluck('slug')->all();
        $novas = [];

        $condicoes = [
            'primeiro-passo' => fn () => $user->checkins()->exists(),
            'chama-acesa' => fn () => $this->streak($user) >= 7,
            'constancia-de-ferro' => fn () => $this->streak($user) >= 30,
            'circulo-completo' => fn () => $user->jornadas()->where('status', 'concluida')->exists(),
            'renascimento' => fn () => $user->auraScores()->count() >= 2,
            'explorador' => fn () => (
                $user->progressos()->where('concluida', true)->count()
                + $user->progressoAudios()->where('concluido', true)->count()
            ) >= 10,
        ];

        foreach ($condicoes as $slug => $condicao) {
            if (in_array($slug, $conquistadas, true) || ! $condicao()) {
                continue;
            }

            $conquista = Conquista::where('slug', $slug)->first();
            if ($conquista) {
                $user->conquistas()->attach($conquista->id, ['conquistado_em' => now()]);
                $novas[] = $slug;
            }
        }

        return $novas;
    }
}
