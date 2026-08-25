<?php

namespace Database\Seeders;

use App\Models\Dimensao;
use Illuminate\Database\Seeder;

class DimensaoSeeder extends Seeder
{
    /**
     * As 5 dimensoes fixas do Aura Score (PRD secao 4).
     */
    public const DIMENSOES = [
        ['slug' => 'prosperidade', 'nome' => 'Prosperidade', 'ordem' => 1],
        ['slug' => 'relacionamentos', 'nome' => 'Relacionamentos', 'ordem' => 2],
        ['slug' => 'saude-energia', 'nome' => 'Saúde e Energia', 'ordem' => 3],
        ['slug' => 'proposito', 'nome' => 'Propósito e Carreira', 'ordem' => 4],
        ['slug' => 'mentalidade', 'nome' => 'Mentalidade e Paz interior', 'ordem' => 5],
    ];

    public function run(): void
    {
        foreach (self::DIMENSOES as $dimensao) {
            Dimensao::updateOrCreate(['slug' => $dimensao['slug']], $dimensao);
        }
    }
}
