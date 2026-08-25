<?php

namespace App\Services;

use App\Models\AuraConversa;
use Illuminate\Support\Str;

/**
 * Classificacao deterministica de conversas por palavras-chave (PRD secao 5).
 * "crise" nunca e sobrescrita (vem do detector do AuraChatService).
 */
class ClassificadorConversa
{
    private const TEMAS = [
        'duvida-plataforma' => ['plataforma', 'login', 'senha', 'assinatura', 'pagamento', 'cancelar', 'aplicativo', 'nao carrega', 'não carrega', 'bug', 'erro no site'],
        'prosperidade' => ['dinheiro', 'renda', 'financeir', 'prosperidade', 'abundancia', 'abundância', 'salario', 'salário', 'divida', 'dívida', 'emprego', 'negocio', 'negócio'],
        'relacionamentos' => ['relacionamento', 'esposa', 'marido', 'namorad', 'casamento', 'familia', 'família', 'amizade', 'amor', 'solidao', 'solidão'],
        'saude' => ['saude', 'saúde', 'doenca', 'doença', 'energia', 'sono', 'cansaco', 'cansaço', 'corpo', 'exercicio', 'exercício', 'alimentacao', 'alimentação'],
        'proposito' => ['proposito', 'propósito', 'carreira', 'vocacao', 'vocação', 'missao', 'missão', 'direcao', 'direção', 'sentido da vida'],
        'mentalidade' => ['ansiedade', 'medo', 'crenca', 'crença', 'mente', 'foco', 'disciplina', 'paz', 'meditacao', 'meditação', 'pensamento'],
    ];

    public function classificar(AuraConversa $conversa): void
    {
        if ($conversa->classificacao === 'crise') {
            return;
        }

        $texto = Str::lower($conversa->mensagens()->where('papel', 'user')->pluck('conteudo')->implode(' '));

        $melhorTema = null;
        $melhorPontuacao = 0;

        foreach (self::TEMAS as $tema => $termos) {
            $pontuacao = 0;
            foreach ($termos as $termo) {
                $pontuacao += substr_count($texto, $termo);
            }
            if ($pontuacao > $melhorPontuacao) {
                $melhorPontuacao = $pontuacao;
                $melhorTema = $tema;
            }
        }

        if ($melhorTema) {
            $conversa->update(['classificacao' => $melhorTema]);
        }
    }
}
