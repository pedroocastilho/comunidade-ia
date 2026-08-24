<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Cria/atualiza o usuario admin que acessa o painel Filament (/admin).
     * Credenciais vem do .env (ADMIN_EMAIL, ADMIN_PASSWORD) com fallback de dev.
     * TROCAR a senha em producao.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@comunidade.local')],
            [
                'name' => 'Administrador',
                'password' => env('ADMIN_PASSWORD', 'admin12345'),
                'role' => 'admin',
                'tem_acesso' => true,
            ],
        );
    }
}
