<?php

namespace Database\Factories;

use App\Models\JornadaTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class JornadaTemplateDiaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'template_id' => JornadaTemplate::factory(),
            'dia' => 1,
            'etapa' => 'Consciencia',
            'acao_texto' => fake()->sentence(),
        ];
    }
}
