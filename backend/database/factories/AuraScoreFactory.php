<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuraScoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'score_global' => fake()->numberBetween(0, 100),
            'scores_dimensoes' => [
                'prosperidade' => 50,
                'relacionamentos' => 50,
                'saude-energia' => 50,
                'proposito' => 50,
                'mentalidade' => 50,
            ],
            'ponto_atencao' => 'prosperidade',
            'padroes' => ['base_solida'],
            'calculado_em' => now(),
        ];
    }
}
