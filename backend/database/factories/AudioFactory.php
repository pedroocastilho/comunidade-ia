<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AudioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tipo' => fake()->randomElement(['frequencia', 'meditacao', 'ritual']),
            'titulo' => fake()->sentence(3),
            'descricao' => fake()->paragraph(),
            'duracao' => fake()->numberBetween(120, 1800),
            'status' => 'publicado',
            'ordem' => 0,
        ];
    }
}
