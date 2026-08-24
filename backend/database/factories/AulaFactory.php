<?php

namespace Database\Factories;

use App\Models\Modulo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AulaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'modulo_id' => Modulo::factory(),
            'titulo' => fake()->sentence(3),
            'descricao' => fake()->paragraph(),
            'duracao' => fake()->numberBetween(120, 3600),
            'ordem' => 0,
        ];
    }
}
