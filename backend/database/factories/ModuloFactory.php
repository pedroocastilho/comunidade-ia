<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuloFactory extends Factory
{
    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'titulo' => fake()->sentence(2),
            'ordem' => 0,
        ];
    }
}
