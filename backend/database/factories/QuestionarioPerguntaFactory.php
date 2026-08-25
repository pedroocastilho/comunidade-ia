<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionarioPerguntaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ordem' => fake()->unique()->numberBetween(1, 1000),
            'tipo' => 'escala',
            'texto' => fake()->sentence(),
            'obrigatoria' => true,
            'alimenta_memoria' => false,
        ];
    }
}
