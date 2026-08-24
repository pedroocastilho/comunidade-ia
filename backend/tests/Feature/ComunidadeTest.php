<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\Aviso;
use App\Models\Comentario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComunidadeTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioComAcesso(): User
    {
        return User::factory()->create(['tem_acesso' => true]);
    }

    public function test_criar_e_listar_comentario_da_aula(): void
    {
        $user = $this->usuarioComAcesso();
        $aula = Aula::factory()->create();

        $this->actingAs($user)
            ->postJson("/api/v1/aulas/{$aula->id}/comentarios", ['texto' => 'Otima aula!'])
            ->assertCreated()
            ->assertJsonPath('data.texto', 'Otima aula!')
            ->assertJsonPath('data.autor.id', $user->id);

        $this->actingAs($user)
            ->getJson("/api/v1/aulas/{$aula->id}/comentarios")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_autor_pode_excluir_o_proprio_comentario(): void
    {
        $user = $this->usuarioComAcesso();
        $aula = Aula::factory()->create();
        $comentario = Comentario::create([
            'user_id' => $user->id,
            'aula_id' => $aula->id,
            'texto' => 'meu comentario',
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/v1/comentarios/{$comentario->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }

    public function test_nao_pode_excluir_comentario_de_outro(): void
    {
        $dono = $this->usuarioComAcesso();
        $outro = $this->usuarioComAcesso();
        $aula = Aula::factory()->create();
        $comentario = Comentario::create([
            'user_id' => $dono->id,
            'aula_id' => $aula->id,
            'texto' => 'comentario do dono',
        ]);

        $this->actingAs($outro)
            ->deleteJson("/api/v1/comentarios/{$comentario->id}")
            ->assertForbidden();
    }

    public function test_mural_lista_apenas_avisos_publicados(): void
    {
        $autor = User::factory()->create(['role' => 'admin']);
        Aviso::create([
            'titulo' => 'Publicado',
            'corpo' => 'texto',
            'autor_id' => $autor->id,
            'publicado_em' => now()->subDay(),
        ]);
        Aviso::create([
            'titulo' => 'Agendado',
            'corpo' => 'texto',
            'autor_id' => $autor->id,
            'publicado_em' => now()->addDay(),
        ]);
        Aviso::create([
            'titulo' => 'Rascunho',
            'corpo' => 'texto',
            'autor_id' => $autor->id,
            'publicado_em' => null,
        ]);

        $this->actingAs($this->usuarioComAcesso())
            ->getJson('/api/v1/avisos')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Publicado');
    }
}
