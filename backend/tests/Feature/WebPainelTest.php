<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WebPainelTest extends TestCase
{
    use RefreshDatabase;

    private function aluno(): User
    {
        return User::factory()->create(['tem_acesso' => true]);
    }

    public function test_home_web_renderiza_para_quem_tem_acesso(): void
    {
        $this->actingAs($this->aluno())->get('/inicio')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('App/Home'));
    }

    public function test_sem_acesso_e_redirecionado(): void
    {
        $user = User::factory()->create(['tem_acesso' => false]);

        $this->actingAs($user)->get('/inicio')->assertRedirect(route('sem-acesso'));
    }

    public function test_catalogo_e_detalhe_e_player_renderizam(): void
    {
        $user = $this->aluno();
        $curso = Curso::factory()->create(['status' => 'publicado', 'slug' => 'curso-web']);
        $modulo = Modulo::factory()->for($curso)->create();
        $aula = Aula::factory()->for($modulo)->create();

        $this->actingAs($user)->get('/cursos')
            ->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Cursos'));

        $this->actingAs($user)->get('/cursos/curso-web')
            ->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Curso'));

        $this->actingAs($user)->get("/aulas/{$aula->id}")
            ->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Player'));
    }

    public function test_concluir_aula_pela_web(): void
    {
        $user = $this->aluno();
        $aula = Aula::factory()->create();

        $this->actingAs($user)->post("/aulas/{$aula->id}/concluir")->assertRedirect();

        $this->assertDatabaseHas('progresso_aulas', [
            'user_id' => $user->id,
            'aula_id' => $aula->id,
            'concluida' => true,
        ]);
    }
}
