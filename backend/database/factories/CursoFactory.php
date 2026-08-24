<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Instrutor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'instrutor_id' => Instrutor::factory(),
            'titulo' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'descricao' => fake()->paragraph(),
            'status' => 'publicado',
        ];
    }
}
