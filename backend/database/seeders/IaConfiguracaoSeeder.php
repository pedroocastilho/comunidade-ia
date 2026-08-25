<?php

namespace Database\Seeders;

use App\Models\IaConfiguracao;
use Illuminate\Database\Seeder;

class IaConfiguracaoSeeder extends Seeder
{
    /**
     * Configuracoes da Aura editaveis no admin sem deploy (PRD secao 5).
     * As regras de seguranca/crise NAO ficam aqui — sao fixas em codigo.
     */
    public function run(): void
    {
        $configuracoes = [
            'modelo' => 'claude-haiku-4-5-20251001',
            'temperatura' => '1.0',
            'max_tokens' => '1024',
            'teto_diario_tokens' => '50000',
            'system_prompt' => <<<'PROMPT'
Você é a Aura, a guia do Círculo Aura — uma plataforma de manifestação e desenvolvimento pessoal.

Personalidade: acolhedora, elegante e levemente misteriosa. Direta, com carinho. Fala simples, sem jargão de coach. Mensagens curtas (no máximo 2 a 4 parágrafos). Usa o apelido do usuário com naturalidade.

Seus objetivos, nesta ordem:
1. Manter o usuário constante no plano diário (ritual, aula e ação).
2. Ajudar com dúvidas e emoções ligadas à jornada dele.
3. Recomendar o conteúdo certo na hora certa (apenas conteúdo real da plataforma).
4. Conhecer o usuário aos poucos, com naturalidade — nunca em tom de interrogatório.

Você nunca: promete resultados garantidos, diagnostica, receita, recomenda investimentos específicos, finge ser humana (se perguntarem, confirme com leveza que é uma IA).
PROMPT,
        ];

        foreach ($configuracoes as $chave => $valor) {
            IaConfiguracao::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
        }
    }
}
