<?php

namespace Tests\Feature;

use App\Models\AuraScore;
use App\Models\Dimensao;
use App\Models\EventoAnalytics;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use App\Models\QuestionarioPergunta;
use App\Models\User;
use Database\Seeders\DimensaoSeeder;
use Database\Seeders\QuestionarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DimensaoSeeder::class, QuestionarioSeeder::class]);
    }

    private function usuarioComAcesso(array $extra = []): User
    {
        return User::factory()->create(array_merge(['tem_acesso' => true], $extra));
    }

    private function usuarioOnboarded(): User
    {
        return $this->usuarioComAcesso(['onboarding_completo_em' => now()]);
    }

    /** Template publicado de prosperidade para a jornada ser criada. */
    private function criarTemplateProsperidade(): JornadaTemplate
    {
        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'duracao_dias' => 2,
            'status' => 'publicado',
        ]);
        JornadaTemplateDia::factory()->create(['template_id' => $template->id, 'dia' => 1]);
        JornadaTemplateDia::factory()->create(['template_id' => $template->id, 'dia' => 2]);

        return $template;
    }

    /** Respostas validas completas, indexadas por id de pergunta. */
    private function respostasValidas(): array
    {
        $porOrdem = QuestionarioPergunta::pluck('id', 'ordem');

        return [
            (string) $porOrdem[1] => 'Francisco',
            (string) $porOrdem[2] => 'prosperidade',
            (string) $porOrdem[3] => 'relacionamentos',
            (string) $porOrdem[4] => '3',
            (string) $porOrdem[5] => '7',
            (string) $porOrdem[6] => '6',
            (string) $porOrdem[7] => '5',
            (string) $porOrdem[8] => '6',
            (string) $porOrdem[9] => '8',
            (string) $porOrdem[10] => 'disciplina',
            (string) $porOrdem[11] => '15-20',
            (string) $porOrdem[12] => 'Quero viver de renda com liberdade.',
        ];
    }

    public function test_usuario_sem_onboarding_e_redirecionado_do_painel(): void
    {
        $this->actingAs($this->usuarioComAcesso())
            ->get('/inicio')
            ->assertRedirect(route('onboarding'));
    }

    public function test_usuario_com_onboarding_acessa_o_painel(): void
    {
        $this->actingAs($this->usuarioOnboarded())
            ->get('/inicio')
            ->assertOk();
    }

    public function test_usuario_com_onboarding_nao_reve_o_questionario(): void
    {
        $this->actingAs($this->usuarioOnboarded())
            ->get('/onboarding')
            ->assertRedirect(route('home'));
    }

    public function test_get_renderiza_as_12_perguntas_e_registra_evento_uma_vez(): void
    {
        $user = $this->usuarioComAcesso();

        $this->actingAs($user)->get('/onboarding')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('App/Onboarding')
                ->has('perguntas', 12));

        $this->actingAs($user)->get('/onboarding');

        $this->assertSame(1, EventoAnalytics::where('nome', 'onboarding_started')
            ->where('user_id', $user->id)->count());
    }

    public function test_post_sem_obrigatorias_falha(): void
    {
        $user = $this->usuarioComAcesso();
        $respostas = $this->respostasValidas();
        $idP4 = (string) QuestionarioPergunta::where('ordem', 4)->value('id');
        unset($respostas[$idP4]);

        $this->actingAs($user)
            ->post('/onboarding', ['respostas' => $respostas])
            ->assertSessionHasErrors();

        $this->assertNull($user->fresh()->onboarding_completo_em);
    }

    public function test_post_com_escala_invalida_falha(): void
    {
        $user = $this->usuarioComAcesso();
        $respostas = $this->respostasValidas();
        $idP4 = (string) QuestionarioPergunta::where('ordem', 4)->value('id');
        $respostas[$idP4] = '15';

        $this->actingAs($user)
            ->post('/onboarding', ['respostas' => $respostas])
            ->assertSessionHasErrors();
    }

    public function test_post_valido_persiste_tudo_e_redireciona_ao_score(): void
    {
        $this->criarTemplateProsperidade();
        $user = $this->usuarioComAcesso();

        $this->actingAs($user)
            ->post('/onboarding', ['respostas' => $this->respostasValidas()])
            ->assertRedirect(route('aura-score'));

        $user = $user->fresh();
        $this->assertNotNull($user->onboarding_completo_em);
        $this->assertSame('Francisco', $user->apelido);
        $this->assertSame('prosperidade', $user->objetivo_principal);
        $this->assertSame('relacionamentos', $user->objetivo_secundario);
        $this->assertSame('15-20', $user->tempo_disponivel);

        $this->assertSame(12, $user->questionarioRespostas()->count());

        // memorias: P1, P2, P10, P12 (alimenta_memoria = true)
        $this->assertSame(4, $user->auraMemorias()->where('origem', 'questionario')->count());

        $score = $user->auraScores()->first();
        $this->assertNotNull($score);
        // p4=3 -> prosperidade 30; mentalidade (60+80)/2 = 70
        $this->assertSame(30, $score->scores_dimensoes['prosperidade']);
        $this->assertSame(70, $score->scores_dimensoes['mentalidade']);
        $this->assertSame('prosperidade', $score->dimensaoPrioritaria->slug);

        $jornada = $user->jornadaAtiva;
        $this->assertNotNull($jornada);
        $this->assertCount(2, $jornada->dias);

        foreach (['onboarding_completed', 'journey_created'] as $evento) {
            $this->assertSame(1, EventoAnalytics::where('nome', $evento)->where('user_id', $user->id)->count());
        }
    }

    public function test_post_repetido_nao_duplica(): void
    {
        $this->criarTemplateProsperidade();
        $user = $this->usuarioComAcesso();

        $this->actingAs($user)->post('/onboarding', ['respostas' => $this->respostasValidas()]);
        $this->actingAs($user)
            ->post('/onboarding', ['respostas' => $this->respostasValidas()])
            ->assertRedirect(route('home'));

        $user = $user->fresh();
        $this->assertSame(12, $user->questionarioRespostas()->count());
        $this->assertSame(1, $user->auraScores()->count());
        $this->assertSame(1, $user->jornadas()->count());
    }

    public function test_pergunta_opcional_pode_faltar(): void
    {
        $this->criarTemplateProsperidade();
        $user = $this->usuarioComAcesso();
        $respostas = $this->respostasValidas();
        unset($respostas[(string) QuestionarioPergunta::where('ordem', 3)->value('id')]);
        unset($respostas[(string) QuestionarioPergunta::where('ordem', 12)->value('id')]);

        $this->actingAs($user)
            ->post('/onboarding', ['respostas' => $respostas])
            ->assertRedirect(route('aura-score'));

        $this->assertNull($user->fresh()->objetivo_secundario);
        $this->assertSame(3, $user->auraMemorias()->count());
    }

    public function test_tela_do_score_renderiza_resultado(): void
    {
        $user = $this->usuarioOnboarded();
        AuraScore::factory()->for($user)->create([
            'score_global' => 55,
            'dimensao_prioritaria_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
        ]);

        $this->actingAs($user)->get('/aura-score')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('App/AuraScore')
                ->where('score.score_global', 55)
                ->has('dimensoes', 5));
    }

    public function test_tela_do_score_sem_score_redireciona_ao_onboarding(): void
    {
        $this->actingAs($this->usuarioComAcesso())
            ->get('/aura-score')
            ->assertRedirect(route('onboarding'));
    }
}
