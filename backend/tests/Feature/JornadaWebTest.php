<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Aula;
use App\Models\Dimensao;
use App\Models\EventoAnalytics;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use App\Models\User;
use App\Services\JornadaService;
use Database\Seeders\DimensaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class JornadaWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DimensaoSeeder::class);
    }

    /** Aluno onboarded com jornada ativa de 3 dias (ritual + aula + acao no dia 1). */
    private function alunoComJornada(): array
    {
        $user = User::factory()->create([
            'tem_acesso' => true,
            'onboarding_completo_em' => now(),
            'apelido' => 'Chico',
            'objetivo_principal' => 'prosperidade',
        ]);

        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'duracao_dias' => 3,
            'status' => 'publicado',
        ]);
        $audio = Audio::factory()->create(['tipo' => 'ritual', 'titulo' => 'Ritual da Manha']);
        $aula = Aula::factory()->create(['titulo' => 'Aula do Dia']);

        foreach (range(1, 3) as $dia) {
            JornadaTemplateDia::factory()->create([
                'template_id' => $template->id,
                'dia' => $dia,
                'etapa' => 'Consciencia',
                'ritual_audio_id' => $audio->id,
                'aula_id' => $aula->id,
                'acao_texto' => 'Anote 3 gratidoes',
            ]);
        }

        $jornada = app(JornadaService::class)->criarParaUsuario($user);

        return [$user, $jornada, $audio, $aula];
    }

    public function test_home_renderiza_o_plano_do_dia(): void
    {
        [$user] = $this->alunoComJornada();

        $this->actingAs($user)->get('/inicio')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Home')
                ->where('jornada.dia', 1)
                ->where('jornada.total_dias', 3)
                ->where('jornada.etapa', 'Consciencia')
                ->where('atividades.ritual.titulo', 'Ritual da Manha')
                ->where('atividades.aula.titulo', 'Aula do Dia')
                ->where('atividades.acao.texto', 'Anote 3 gratidoes')
                ->where('apelido', 'Chico')
                ->has('progresso_semana', 7));
    }

    public function test_home_sem_jornada_nao_quebra(): void
    {
        $user = User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);

        $this->actingAs($user)->get('/inicio')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Home')
                ->where('jornada', null));
    }

    public function test_daily_plan_opened_e_registrado_uma_vez_por_dia(): void
    {
        [$user] = $this->alunoComJornada();

        $this->actingAs($user)->get('/inicio');
        $this->actingAs($user)->get('/inicio');

        $this->assertSame(1, EventoAnalytics::where('nome', 'daily_plan_opened')
            ->where('user_id', $user->id)->count());
    }

    public function test_concluir_ritual_e_acao_marca_e_avanca_com_aula(): void
    {
        [$user, $jornada, , $aula] = $this->alunoComJornada();

        $this->actingAs($user)->post('/jornada/atividade', ['tipo' => 'ritual'])->assertRedirect();
        $this->actingAs($user)->post('/jornada/atividade', ['tipo' => 'acao'])->assertRedirect();

        $dia = $jornada->fresh()->dias()->where('dia', 1)->first();
        $this->assertTrue($dia->ritual_concluido);
        $this->assertTrue($dia->acao_concluida);
        $this->assertSame(1, $jornada->fresh()->dia_atual); // aula ainda falta

        // concluir a aula pela rota existente marca a atividade e avanca o dia
        $this->actingAs($user)->post("/aulas/{$aula->id}/concluir")->assertRedirect();

        $this->assertTrue($dia->fresh()->aula_concluida);
        $this->assertSame(2, $jornada->fresh()->dia_atual);
        $this->assertSame(1, EventoAnalytics::where('nome', 'journey_day_completed')
            ->where('user_id', $user->id)->count());
    }

    public function test_checkin_registra_evento_e_avanca(): void
    {
        [$user, $jornada] = $this->alunoComJornada();

        $this->actingAs($user)
            ->post('/checkin', ['humor' => 4, 'texto' => 'dia bom'])
            ->assertRedirect();

        $this->assertSame(1, $user->checkins()->count());
        $this->assertSame(2, $jornada->fresh()->dia_atual);
        $this->assertSame(1, EventoAnalytics::where('nome', 'daily_checkin_completed')
            ->where('user_id', $user->id)->count());
    }

    public function test_checkin_valida_humor(): void
    {
        [$user] = $this->alunoComJornada();

        $this->actingAs($user)->post('/checkin', ['humor' => 9])->assertSessionHasErrors();
    }

    public function test_pagina_jornada_lista_os_dias(): void
    {
        [$user] = $this->alunoComJornada();

        $this->actingAs($user)->get('/jornada')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Jornada')
                ->has('dias', 3)
                ->where('dias.0.atual', true));
    }
}
