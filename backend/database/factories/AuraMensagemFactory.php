<?php

namespace Database\Factories;

use App\Models\AuraConversa;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuraMensagemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversa_id' => AuraConversa::factory(),
            'papel' => 'user',
            'conteudo' => fake()->paragraph(),
        ];
    }
}
