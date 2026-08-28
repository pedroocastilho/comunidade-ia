<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * O agente passa a se chamar Noah (masculino). Atualiza o system_prompt ja
 * gravado em ia_configuracoes preservando edicoes feitas no admin: so troca
 * os trechos que citam a persona antiga.
 */
return new class extends Migration
{
    private array $trocas = [
        'Você é a Aura, a guia do Círculo Aura' => 'Você é o Noah, o guia do Círculo Aura',
        'nem uma coach de palco' => 'nem um coach de palco',
        'como uma brasileira culta falando' => 'como um brasileiro culto falando',
        '"Quer que eu seja honesta?", "vou ser sincera com você"' => '"Quer que eu seja honesto?", "vou ser sincero com você"',
        'Você fala de si sempre no feminino: "obrigada", "honesta", "sua guia".' => 'Você fala de si sempre no masculino: "obrigado", "honesto", "seu guia". Seu nome é Noah; a plataforma se chama Círculo Aura e a medida da pessoa é o Aura Score — não confunda os dois com você.',
        'receba como anfitriã, nunca como porteira' => 'receba como anfitrião, nunca como porteiro',
        'você não é uma assistente de uso geral' => 'você não é um assistente de uso geral',
    ];

    public function up(): void
    {
        $atual = DB::table('ia_configuracoes')->where('chave', 'system_prompt')->value('valor');
        if ($atual === null) {
            return;
        }

        $novo = str_replace(array_keys($this->trocas), array_values($this->trocas), $atual);
        if ($novo !== $atual) {
            DB::table('ia_configuracoes')->where('chave', 'system_prompt')
                ->update(['valor' => $novo, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        $atual = DB::table('ia_configuracoes')->where('chave', 'system_prompt')->value('valor');
        if ($atual === null) {
            return;
        }

        $antigo = str_replace(array_values($this->trocas), array_keys($this->trocas), $atual);
        DB::table('ia_configuracoes')->where('chave', 'system_prompt')
            ->update(['valor' => $antigo, 'updated_at' => now()]);
    }
};
