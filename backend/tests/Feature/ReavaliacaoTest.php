<?php

namespace Tests\Feature;

use App\Models\AuraScore;
use App\Models\Dimensao;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use App\Models\User;
use App\Services\JornadaService;
use Database\Seeders\DimensaoSeeder;
use Database\Seeders\QuestionarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReavaliacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DimensaoSeeder::class, QuestionarioSeeder::class]);
    }

    /** Usuario com jornada CONCLUIDA e score original salvo. */
    private function usuarioPosJornada(): User
    {
        $user = User::factory()->create([
            'tem_acesso' => true,
            'onboarding_completo_em' => now()->subDays(30),
            'apelido' => 'Chico',
            'objetivo_principal' => 'prosperidade',
        ]);

        AuraScore::factory()->for($user)->create([
            'score_global' => 53,
            'dimensao_prioritaria_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'calculado_em' => now()->subDays(30),
        ]);

        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'duracao_dias' => 1,
            'status' => 'publicado',
        ]);
        JornadaTemplateDia::factory()->create(['template_id' => $template->id, 'dia' => 1]);

        $jornada = app(JornadaService::class)->criarParaUsuario($user);
        $jornada->update(['status' => 'concluida']);

        return $user;
    }

    public function test_pagina_de_reavaliacao_renderiza_as_6_escalas(): void
    {
        $this->actingAs($this->usuarioPosJornada())->get('/reavaliacao')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Reavaliacao')
                ->has('perguntas', 6));
    }

    public function test_reavaliacao_e_bloqueada_com_jornada_ativa(): void
    {
        $user = $this->usuarioPosJornada();
        $user->jornadas()->latest('id')->first()->update(['status' => 'ativa']);

        $this->actingAs($user)->get('/reavaliacao')->assertRedirect(route('home'));
    }

    public function test_post_cria_novo_score_preservando_o_historico(): void
    {
        $user = $this->usuarioPosJornada();

        $this->actingAs($user)
            ->post('/reavaliacao', ['escalas' => ['p4' => 6, 'p5' => 8, 'p6' => 7, 'p7' => 6, 'p8' => 7, 'p9' => 9]])
            ->assertRedirect(route('aura-score'));

        $this->assertSame(2, $user->auraScores()->count());

        $novo = $user->auraScores()->latest('calculado_em')->first();
        // sem objetivo secundario: (60*2 + 80 + 70 + 60 + 80) / 6 = 68.33 -> 68
        $this->assertSame(68, $novo->score_global);
    }

    public function test_tela_do_score_mostra_o_delta_da_evolucao(): void
    {
        $user = $this->usuarioPosJornada();
        $this->actingAs($user)->post('/reavaliacao', ['escalas' => ['p4' => 6, 'p5' => 8, 'p6' => 7, 'p7' => 6, 'p8' => 7, 'p9' => 9]]);

        $this->actingAs($user)->get('/aura-score')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('score.score_global', 68)
                ->where('score_anterior', 53));
    }

    public function test_post_valida_escalas(): void
    {
        $this->actingAs($this->usuarioPosJornada())
            ->post('/reavaliacao', ['escalas' => ['p4' => 15]])
            ->assertSessionHasErrors();
    }
}
