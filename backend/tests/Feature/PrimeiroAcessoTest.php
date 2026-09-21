<?php

namespace Tests\Feature;

use App\Http\Middleware\SenhaPadraoDefinida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Comprador criado pelo webhook entra com a senha padrao e e obrigado a
 * definir a propria antes de navegar.
 */
class PrimeiroAcessoTest extends TestCase
{
    use RefreshDatabase;

    private function compradorComSenhaPadrao(): User
    {
        return User::factory()->create([
            'password' => SenhaPadraoDefinida::SENHA_PADRAO,
            'tem_acesso' => true,
            'onboarding_completo_em' => now(),
        ]);
    }

    public function test_webhook_cria_conta_com_senha_padrao(): void
    {
        config(['services.webhook_pagamento.token' => 'token']);

        $this->postJson('/api/webhooks/pagamento/generico', [
            'evento' => 'assinatura_ativa', 'email' => 'novo@exemplo.com',
        ], ['X-Webhook-Token' => 'token'])->assertOk();

        $user = User::where('email', 'novo@exemplo.com')->first();
        $this->assertTrue(Hash::check(SenhaPadraoDefinida::SENHA_PADRAO, $user->password));
    }

    public function test_login_com_senha_padrao_forca_definir_senha(): void
    {
        $user = $this->compradorComSenhaPadrao();

        $this->post('/login', ['email' => $user->email, 'password' => SenhaPadraoDefinida::SENHA_PADRAO]);

        $this->get('/inicio')->assertRedirect(route('senha.definir'));
        $this->get('/circulo')->assertRedirect(route('senha.definir'));
        $this->get(route('senha.definir'))->assertOk();
    }

    public function test_definir_senha_libera_navegacao_e_nao_aceita_a_padrao(): void
    {
        $user = $this->compradorComSenhaPadrao();
        $this->post('/login', ['email' => $user->email, 'password' => SenhaPadraoDefinida::SENHA_PADRAO]);

        // Recusar manter a senha padrao
        $this->post(route('senha.salvar'), [
            'password' => SenhaPadraoDefinida::SENHA_PADRAO,
            'password_confirmation' => SenhaPadraoDefinida::SENHA_PADRAO,
        ])->assertSessionHasErrors('password');

        $this->post(route('senha.salvar'), [
            'password' => 'minha-senha-nova-1',
            'password_confirmation' => 'minha-senha-nova-1',
        ])->assertRedirect(route('home'));

        $this->assertTrue(Hash::check('minha-senha-nova-1', $user->fresh()->password));
        $this->get('/inicio')->assertOk();
    }

    public function test_api_nao_emite_token_com_senha_padrao(): void
    {
        $user = $this->compradorComSenhaPadrao();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => SenhaPadraoDefinida::SENHA_PADRAO,
        ])->assertForbidden();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_usuario_com_senha_propria_nao_e_redirecionado(): void
    {
        $user = User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);

        $this->actingAs($user)->get('/inicio')->assertOk();
        $this->actingAs($user)->get(route('senha.definir'))->assertRedirect(route('home'));
    }
}
