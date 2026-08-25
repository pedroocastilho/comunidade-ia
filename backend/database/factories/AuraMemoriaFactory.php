<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuraMemoriaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tipo' => 'fato',
            'conteudo' => fake()->sentence(),
            'origem' => 'questionario',
            'ativo' => true,
        ];
    }
}
