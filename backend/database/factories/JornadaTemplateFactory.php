<?php

namespace Database\Factories;

use App\Models\Dimensao;
use Illuminate\Database\Eloquent\Factories\Factory;

class JornadaTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dimensao_id' => Dimensao::firstOrCreate(
                ['slug' => 'prosperidade'],
                ['nome' => 'Prosperidade', 'ordem' => 1]
            )->id,
            'titulo' => fake()->sentence(3),
            'descricao' => fake()->paragraph(),
            'duracao_dias' => 30,
            'status' => 'publicado',
        ];
    }
}
