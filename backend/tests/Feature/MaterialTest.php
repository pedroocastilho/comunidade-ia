<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use RefreshDatabase;

    private string $arquivo;

    protected function setUp(): void
    {
        parent::setUp();
        File::ensureDirectoryExists(storage_path('app/materiais'));
        $this->arquivo = storage_path('app/materiais/teste-material.pdf');
        file_put_contents($this->arquivo, '%PDF-1.4 teste');
    }

    protected function tearDown(): void
    {
        @unlink($this->arquivo);
        parent::tearDown();
    }

    public function test_visitante_nao_acessa_material(): void
    {
        $this->get('/materiais/teste-material')->assertRedirect(route('login'));
    }

    public function test_assinante_ve_o_material_inline(): void
    {
        $user = User::factory()->create(['tem_acesso' => true]);

        $this->actingAs($user)
            ->get('/materiais/teste-material')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="teste-material.pdf"');
    }

    public function test_usuario_sem_assinatura_nao_acessa_material(): void
    {
        $user = User::factory()->create(['tem_acesso' => false]);

        $this->actingAs($user)
            ->get('/materiais/teste-material')
            ->assertRedirect(route('sem-acesso'));
    }

    public function test_material_inexistente_retorna_404(): void
    {
        $user = User::factory()->create(['tem_acesso' => true]);

        $this->actingAs($user)->get('/materiais/nao-existe')->assertNotFound();
    }
}
