<?php

namespace Tests\Feature;

use App\Models\Denuncia;
use App\Models\Post;
use App\Models\PostComentario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CirculoTest extends TestCase
{
    use RefreshDatabase;

    private function aluno(): User
    {
        return User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);
    }

    public function test_feed_lista_publicados_com_fixados_primeiro(): void
    {
        $user = $this->aluno();
        Post::factory()->create(['corpo' => 'post normal']);
        Post::factory()->create(['corpo' => 'post oculto', 'status' => 'oculto']);
        Post::factory()->create(['corpo' => 'post fixado', 'fixado' => true]);

        $this->actingAs($user)->get('/circulo')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Circulo')
                ->has('posts', 2)
                ->where('posts.0.corpo', 'post fixado')
                ->where('posts.0.fixado', true));
    }

    public function test_publicar_reagir_e_comentar(): void
    {
        $user = $this->aluno();

        $this->actingAs($user)->post('/circulo/posts', ['corpo' => 'Dia 12 e a mente ja esta outra. ✦'])->assertRedirect();
        $post = Post::first();
        $this->assertSame($user->id, $post->user_id);

        $this->actingAs($user)->post("/circulo/posts/{$post->id}/reagir")->assertRedirect();
        $this->assertSame(1, $post->reacoes()->count());

        // reagir de novo desfaz (toggle)
        $this->actingAs($user)->post("/circulo/posts/{$post->id}/reagir");
        $this->assertSame(0, $post->reacoes()->count());

        $this->actingAs($user)->post("/circulo/posts/{$post->id}/comentar", ['texto' => 'que bom te ver aqui'])->assertRedirect();
        $this->assertSame(1, PostComentario::count());
    }

    public function test_validacoes_de_conteudo(): void
    {
        $user = $this->aluno();

        $this->actingAs($user)->post('/circulo/posts', ['corpo' => ''])->assertSessionHasErrors();
        $this->actingAs($user)->post('/circulo/posts', ['corpo' => str_repeat('a', 2001)])->assertSessionHasErrors();
    }

    public function test_post_oculto_nao_recebe_interacao(): void
    {
        $user = $this->aluno();
        $post = Post::factory()->create(['status' => 'oculto']);

        $this->actingAs($user)->post("/circulo/posts/{$post->id}/reagir")->assertNotFound();
        $this->actingAs($user)->post("/circulo/posts/{$post->id}/comentar", ['texto' => 'oi'])->assertNotFound();
    }

    public function test_denuncia_registra_uma_vez(): void
    {
        $user = $this->aluno();
        $post = Post::factory()->create();

        $this->actingAs($user)->post('/circulo/denunciar', ['tipo' => 'post', 'id' => $post->id, 'motivo' => 'spam'])->assertRedirect();
        $this->actingAs($user)->post('/circulo/denunciar', ['tipo' => 'post', 'id' => $post->id]);

        $this->assertSame(1, Denuncia::count());
        $this->assertFalse(Denuncia::first()->resolvida);
    }

    public function test_denuncia_de_alvo_inexistente_e_404(): void
    {
        $this->actingAs($this->aluno())
            ->post('/circulo/denunciar', ['tipo' => 'post', 'id' => 999])
            ->assertNotFound();
    }
}
