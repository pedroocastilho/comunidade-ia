<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConteudoTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioComAcesso(): User
    {
        return User::factory()->create(['tem_acesso' => true]);
    }

    public function test_listagem_de_cursos_traz_apenas_publicados(): void
    {
        Curso::factory()->create(['titulo' => 'Publicado', 'status' => 'publicado']);
        Curso::factory()->create(['titulo' => 'Rascunho', 'status' => 'rascunho']);

        $resp = $this->actingAs($this->usuarioComAcesso())->getJson('/api/v1/cursos');

        $resp->assertOk()->assertJsonCount(1, 'data');
        $resp->assertJsonPath('data.0.titulo', 'Publicado');
    }

    public function test_busca_filtra_por_titulo(): void
    {
        Curso::factory()->create(['titulo' => 'Prompts Avancados', 'status' => 'publicado']);
        Curso::factory()->create(['titulo' => 'Automacao com IA', 'status' => 'publicado']);

        $this->actingAs($this->usuarioComAcesso())
            ->getJson('/api/v1/cursos?busca=Prompts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Prompts Avancados');
    }

    public function test_em_alta_ordena_por_views(): void
    {
        Curso::factory()->create(['titulo' => 'Menos', 'status' => 'publicado', 'views' => 5]);
        Curso::factory()->create(['titulo' => 'Mais', 'status' => 'publicado', 'views' => 50]);

        $this->actingAs($this->usuarioComAcesso())
            ->getJson('/api/v1/em-alta')
            ->assertOk()
            ->assertJsonPath('data.0.titulo', 'Mais');
    }

    public function test_detalhe_do_curso_traz_modulos_aulas_e_progresso(): void
    {
        $curso = Curso::factory()->create(['status' => 'publicado', 'slug' => 'curso-teste']);
        $modulo = Modulo::factory()->for($curso)->create();
        Aula::factory()->for($modulo)->create();

        $resp = $this->actingAs($this->usuarioComAcesso())->getJson('/api/v1/cursos/curso-teste');

        $resp->assertOk()
            ->assertJsonPath('data.slug', 'curso-teste')
            ->assertJsonPath('data.progresso_percentual', 0)
            ->assertJsonCount(1, 'data.modulos')
            ->assertJsonCount(1, 'data.modulos.0.aulas');
    }

    public function test_abrir_aula_retorna_url_assinada_do_bunny(): void
    {
        $modulo = Modulo::factory()->create();
        $aula = Aula::factory()->for($modulo)->create(['bunny_video_id' => 'video-abc']);

        $resp = $this->actingAs($this->usuarioComAcesso())->getJson("/api/v1/aulas/{$aula->id}");

        $resp->assertOk()->assertJsonPath('data.id', $aula->id);
        $this->assertStringContainsString('iframe.mediadelivery.net', $resp->json('data.video_embed_url'));
        $this->assertStringContainsString('video-abc', $resp->json('data.video_embed_url'));
    }

    public function test_concluir_aula_marca_progresso_e_afeta_percentual(): void
    {
        $user = $this->usuarioComAcesso();
        $curso = Curso::factory()->create(['status' => 'publicado', 'slug' => 'curso-x']);
        $modulo = Modulo::factory()->for($curso)->create();
        $aula = Aula::factory()->for($modulo)->create();

        $this->actingAs($user)->postJson("/api/v1/aulas/{$aula->id}/concluir")->assertOk();

        $this->assertDatabaseHas('progresso_aulas', [
            'user_id' => $user->id,
            'aula_id' => $aula->id,
            'concluida' => true,
        ]);

        $this->actingAs($user)->getJson('/api/v1/cursos/curso-x')
            ->assertJsonPath('data.progresso_percentual', 100);
    }

    public function test_salvar_posicao_de_progresso(): void
    {
        $user = $this->usuarioComAcesso();
        $aula = Aula::factory()->create();

        $this->actingAs($user)
            ->putJson("/api/v1/aulas/{$aula->id}/progresso", ['posicao_segundos' => 120])
            ->assertOk();

        $this->assertDatabaseHas('progresso_aulas', [
            'user_id' => $user->id,
            'aula_id' => $aula->id,
            'posicao_segundos' => 120,
        ]);
    }

    public function test_download_retorna_url_do_cdn(): void
    {
        $aula = Aula::factory()->create(['bunny_video_id' => 'video-dl']);

        $resp = $this->actingAs($this->usuarioComAcesso())->getJson("/api/v1/aulas/{$aula->id}/download");

        $resp->assertOk();
        $this->assertStringContainsString('video-dl', $resp->json('url'));
        $this->assertStringContainsString('token=', $resp->json('url'));
    }

    public function test_minha_lista_adiciona_lista_e_remove(): void
    {
        $user = $this->usuarioComAcesso();
        $curso = Curso::factory()->create(['status' => 'publicado']);

        $this->actingAs($user)->postJson("/api/v1/minha-lista/{$curso->id}")->assertCreated();
        $this->actingAs($user)->getJson('/api/v1/minha-lista')->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($user)->deleteJson("/api/v1/minha-lista/{$curso->id}")->assertNoContent();
        $this->actingAs($user)->getJson('/api/v1/minha-lista')->assertJsonCount(0, 'data');
    }

    public function test_home_retorna_estrutura_esperada(): void
    {
        Categoria::factory()->create();
        Curso::factory()->create(['status' => 'publicado', 'destaque' => true]);

        $this->actingAs($this->usuarioComAcesso())->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonStructure(['destaque', 'continuar_assistindo', 'trilhas', 'avisos']);
    }

    public function test_sem_acesso_nao_ve_conteudo(): void
    {
        $user = User::factory()->create(['tem_acesso' => false]);

        $this->actingAs($user)->getJson('/api/v1/cursos')->assertForbidden();
    }
}
