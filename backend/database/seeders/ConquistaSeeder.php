<?php

namespace Database\Seeders;

use App\Models\Conquista;
use Illuminate\Database\Seeder;

class ConquistaSeeder extends Seeder
{
    /**
     * Conquistas fixas do Circulo Aura (PRD secao 11, versao enxuta).
     * As condicoes de desbloqueio ficam em GamificacaoService.
     */
    public function run(): void
    {
        $conquistas = [
            ['slug' => 'primeiro-passo', 'nome' => 'Primeiro Passo', 'descricao' => 'Registrou seu primeiro dia no Círculo.', 'icone' => '✦', 'ordem' => 1],
            ['slug' => 'chama-acesa', 'nome' => 'Chama Acesa', 'descricao' => '7 dias seguidos de presença.', 'icone' => '🔥', 'ordem' => 2],
            ['slug' => 'constancia-de-ferro', 'nome' => 'Constância de Ferro', 'descricao' => '30 dias seguidos de presença.', 'icone' => '⚜️', 'ordem' => 3],
            ['slug' => 'circulo-completo', 'nome' => 'Círculo Completo', 'descricao' => 'Concluiu sua primeira jornada de 30 dias.', 'icone' => '◎', 'ordem' => 4],
            ['slug' => 'renascimento', 'nome' => 'Renascimento', 'descricao' => 'Remediu seu Aura Score após uma jornada.', 'icone' => '☀️', 'ordem' => 5],
            ['slug' => 'explorador', 'nome' => 'Explorador do Círculo', 'descricao' => 'Concluiu 10 conteúdos da biblioteca.', 'icone' => '✧', 'ordem' => 6],
        ];

        foreach ($conquistas as $conquista) {
            Conquista::updateOrCreate(['slug' => $conquista['slug']], $conquista);
        }
    }
}
