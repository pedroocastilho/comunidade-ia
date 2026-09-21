<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Estes testes cobrem o fluxo de cadastro em si; a flag fica fechada em producao
        config(['circulo.cadastro_aberto' => true]);
    }

    public function test_cadastro_cria_usuario_sem_acesso_e_retorna_token(): void
    {
        $resp = $this->postJson('/api/v1/auth/register', [
            'name' => 'Pedro Castilho',
            'email' => 'pedro@example.com',
            'phone' => '54999999999',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ]);

        $resp->assertCreated()->assertJsonStructure(['user' => ['id', 'email'], 'token']);
        $this->assertDatabaseHas('users', [
            'email' => 'pedro@example.com',
            'tem_acesso' => false,
            'role' => 'aluno',
        ]);
    }

    public function test_cadastro_exige_email_unico(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'A', 'email' => 'a@a.com',
            'password' => 'senha12345', 'password_confirmation' => 'senha12345',
        ])->assertCreated();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'B', 'email' => 'a@a.com',
            'password' => 'senha12345', 'password_confirmation' => 'senha12345',
        ])->assertStatus(422);
    }
}
