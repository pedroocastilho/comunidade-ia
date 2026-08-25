<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckinFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'humor' => fake()->numberBetween(1, 5),
        ];
    }
}
