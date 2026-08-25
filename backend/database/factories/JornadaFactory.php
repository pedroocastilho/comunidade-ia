<?php

namespace Database\Factories;

use App\Models\JornadaTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JornadaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'template_id' => JornadaTemplate::factory(),
            'dia_atual' => 1,
            'status' => 'ativa',
            'iniciada_em' => now(),
        ];
    }
}
