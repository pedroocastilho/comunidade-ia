<?php

namespace Database\Factories;

use App\Models\Jornada;
use Illuminate\Database\Eloquent\Factories\Factory;

class JornadaDiaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'jornada_id' => Jornada::factory(),
            'dia' => 1,
            'etapa' => 'Consciencia',
            'acao_texto' => fake()->sentence(),
        ];
    }
}
