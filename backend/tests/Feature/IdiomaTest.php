<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdiomaTest extends TestCase
{
    use RefreshDatabase;

    public function test_troca_de_idioma_persiste_na_conta_do_usuario(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/inicio')
            ->post('/idioma', ['locale' => 'es'])
            ->assertRedirect('/inicio');

        $this->assertSame('es', $user->fresh()->locale);
        $this->assertSame('es', session('locale'));
    }

    public function test_idioma_da_conta_vale_em_sessao_nova_de_outro_dispositivo(): void
    {
        $user = User::factory()->create(['locale' => 'es']);

        // Sessao nova (outro dispositivo): sem locale na sessao, vale o da conta
        $this->actingAs($user)->get('/sem-acesso');

        $this->assertSame('es', app()->getLocale());
    }

    public function test_visitante_troca_idioma_apenas_na_sessao(): void
    {
        $this->post('/idioma', ['locale' => 'es'])->assertRedirect();

        $this->assertSame('es', session('locale'));
    }

    public function test_locale_invalido_e_ignorado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/idioma', ['locale' => 'en"><script>']);

        $this->assertNull($user->fresh()->locale);
        $this->assertNull(session('locale'));
    }
}
