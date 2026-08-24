<?php

namespace Database\Factories;

use App\Models\Instrutor;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstrutorFactory extends Factory
{
    protected $model = Instrutor::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'bio' => fake()->sentence(),
        ];
    }
}
