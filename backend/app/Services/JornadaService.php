<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\Dimensao;
use App\Models\Jornada;
use App\Models\JornadaDia;
use App\Models\JornadaTemplate;
use App\Models\User;

/**
 * Motor de jornadas (PRD secao 6): materializa o template do objetivo do
 * usuario e controla o avanco diario. A adaptacao pela IA edita a copia do
 * usuario (jornada_dias), nunca o template.
 */
class JornadaService
{
    /**
     * Cria a jornada do usuario a partir do template publicado da dimensao
     * do objetivo principal. Retorna null se nao houver template.
     */
    public function criarParaUsuario(User $user): ?Jornada
    {
        $template = JornadaTemplate::where('status', 'publicado')
            ->where('dimensao_id', Dimensao::where('slug', $user->objetivo_principal)->value('id'))
            ->orderBy('id')
            ->first();

        if (! $template) {
            return null;
        }

        $jornada = Jornada::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'dia_atual' => 1,
            'status' => 'ativa',
            'iniciada_em' => now(),
        ]);

        $usaVarianteCurta = $user->tempo_disponivel === '5-10';

        foreach ($template->dias as $diaTemplate) {
            $atividades = [
                'ritual_audio_id' => $diaTemplate->ritual_audio_id,
                'aula_id' => $diaTemplate->aula_id,
                'acao_texto' => $diaTemplate->acao_texto,
            ];

            // Variante curta substitui apenas os campos que definir.
            if ($usaVarianteCurta && $diaTemplate->variante_curta) {
                $atividades = array_merge($atividades, array_intersect_key(
                    $diaTemplate->variante_curta,
                    $atividades
                ));
            }

            JornadaDia::create([
                'jornada_id' => $jornada->id,
                'dia' => $diaTemplate->dia,
                'etapa' => $diaTemplate->etapa,
                ...$atividades,
            ]);
        }

        return $jornada->load('dias');
    }

    public function diaAtual(Jornada $jornada): ?JornadaDia
    {
        return $jornada->dias()->where('dia', $jornada->dia_atual)->first();
    }

    /**
     * Marca uma atividade do dia como concluida. $tipo: ritual | aula | acao.
     */
    public function concluirAtividade(JornadaDia $dia, string $tipo): JornadaDia
    {
        $coluna = match ($tipo) {
            'ritual' => 'ritual_concluido',
            'aula' => 'aula_concluida',
            'acao' => 'acao_concluida',
        };

        $dia->update([$coluna => true]);

        return $dia;
    }

    /**
     * Registra (ou atualiza) o check-in do dia-calendario atual.
     */
    public function registrarCheckin(User $user, int $humor, ?string $texto): Checkin
    {
        $jornada = $user->jornadaAtiva;
        $diaAtual = $jornada ? $this->diaAtual($jornada) : null;

        $existente = $user->checkins()->whereDate('created_at', today())->first();

        if ($existente) {
            $existente->update(['humor' => $humor, 'texto' => $texto]);

            return $existente->fresh();
        }

        return Checkin::create([
            'user_id' => $user->id,
            'jornada_dia_id' => $diaAtual?->id,
            'humor' => $humor,
            'texto' => $texto,
        ]);
    }

    /**
     * Avanca o dia da jornada se o dia atual esta completo (3 atividades OU
     * check-in de hoje). No maximo 1 avanco por dia-calendario. Dias sem
     * acesso nao pulam conteudo. Conclui a jornada no ultimo dia.
     */
    public function avancarSeCompleto(Jornada $jornada): bool
    {
        if ($jornada->status !== 'ativa') {
            return false;
        }

        if ($jornada->ultimo_avanco_em?->isToday()) {
            return false;
        }

        $dia = $this->diaAtual($jornada);
        if (! $dia) {
            return false;
        }

        $atividadesCompletas = $dia->ritual_concluido && $dia->aula_concluida && $dia->acao_concluida;
        $checkinHoje = $jornada->user->checkins()
            ->where('jornada_dia_id', $dia->id)
            ->whereDate('created_at', today())
            ->exists();

        if (! $atividadesCompletas && ! $checkinHoje) {
            return false;
        }

        $dia->update(['concluido_em' => now()]);

        $ultimoDia = $jornada->dia_atual >= $jornada->template->duracao_dias;

        $jornada->update([
            'dia_atual' => $ultimoDia ? $jornada->dia_atual : $jornada->dia_atual + 1,
            'status' => $ultimoDia ? 'concluida' : 'ativa',
            'ultimo_avanco_em' => today(),
        ]);

        return true;
    }

    /**
     * Ponte com a biblioteca: ao concluir uma aula, marca a atividade
     * correspondente na jornada ativa, se for a aula do dia.
     */
    public function concluirAulaDaJornada(User $user, int $aulaId): void
    {
        $jornada = $user->jornadaAtiva;
        if (! $jornada) {
            return;
        }

        $dia = $this->diaAtual($jornada);
        if ($dia && $dia->aula_id === $aulaId && ! $dia->aula_concluida) {
            $this->concluirAtividade($dia, 'aula');
        }
    }
}
