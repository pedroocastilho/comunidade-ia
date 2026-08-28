<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MetaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'titulo' => $this->faker->sentence(4),
            'prazo' => now()->addDays(7)->toDateString(),
            'concluida_em' => null,
        ];
    }
}
