<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cadastro publico fechado por padrao (config circulo.cadastro_aberto):
 * conta de membro nasce pelo webhook de pagamento.
 */
class CadastroFechadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_web_retorna_404_com_cadastro_fechado(): void
    {
        config(['circulo.cadastro_aberto' => false]);

        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'X', 'email' => 'x@exemplo.com',
            'password' => 'senha-forte-123', 'password_confirmation' => 'senha-forte-123',
        ])->assertNotFound();
        $this->assertSame(0, User::count());
    }

    public function test_registro_api_retorna_403_com_cadastro_fechado(): void
    {
        config(['circulo.cadastro_aberto' => false]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'X', 'email' => 'x@exemplo.com',
            'password' => 'senha-forte-123', 'password_confirmation' => 'senha-forte-123',
        ])->assertForbidden();
        $this->assertSame(0, User::count());
    }

    public function test_registro_funciona_com_cadastro_aberto(): void
    {
        config(['circulo.cadastro_aberto' => true]);

        $this->post('/register', [
            'name' => 'X', 'email' => 'x@exemplo.com',
            'password' => 'senha-forte-123', 'password_confirmation' => 'senha-forte-123',
        ])->assertRedirect();
        $this->assertSame(1, User::count());
    }
}
