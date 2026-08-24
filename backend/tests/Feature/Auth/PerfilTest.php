<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_retorna_o_usuario_autenticado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_atualizar_dados_do_perfil(): void
    {
        $user = User::factory()->create(['name' => 'Antigo']);

        $this->actingAs($user)->putJson('/api/v1/me', ['name' => 'Novo Nome'])
            ->assertOk()
            ->assertJsonPath('name', 'Novo Nome');
    }

    public function test_troca_de_senha_exige_senha_atual_correta(): void
    {
        $user = User::factory()->create(['password' => 'senha12345']);

        $this->actingAs($user)->putJson('/api/v1/me/password', [
            'senha_atual' => 'senha12345',
            'password' => 'novasenha123',
            'password_confirmation' => 'novasenha123',
        ])->assertOk();

        $this->assertTrue(Hash::check('novasenha123', $user->fresh()->password));
    }

    public function test_troca_de_senha_recusa_senha_atual_errada(): void
    {
        $user = User::factory()->create(['password' => 'senha12345']);

        $this->actingAs($user)->putJson('/api/v1/me/password', [
            'senha_atual' => 'errada',
            'password' => 'novasenha123',
            'password_confirmation' => 'novasenha123',
        ])->assertStatus(422);
    }

    public function test_excluir_conta_remove_o_usuario(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson('/api/v1/me')->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
