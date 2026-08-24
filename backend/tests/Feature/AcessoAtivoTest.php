<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcessoAtivoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sem_acesso_recebe_403_em_rota_protegida(): void
    {
        $user = User::factory()->create(['tem_acesso' => false]);

        $this->actingAs($user)->getJson('/api/v1/categorias')->assertForbidden();
    }

    public function test_com_acesso_ativo_entra_na_rota_protegida(): void
    {
        $user = User::factory()->create([
            'tem_acesso' => true,
            'acesso_expira_em' => now()->addYear(),
        ]);

        $this->actingAs($user)->getJson('/api/v1/categorias')->assertOk();
    }

    public function test_acesso_sem_data_de_expiracao_e_permitido(): void
    {
        $user = User::factory()->create([
            'tem_acesso' => true,
            'acesso_expira_em' => null,
        ]);

        $this->actingAs($user)->getJson('/api/v1/categorias')->assertOk();
    }

    public function test_acesso_expirado_recebe_403(): void
    {
        $user = User::factory()->create([
            'tem_acesso' => true,
            'acesso_expira_em' => now()->subDay(),
        ]);

        $this->actingAs($user)->getJson('/api/v1/categorias')->assertForbidden();
    }
}
