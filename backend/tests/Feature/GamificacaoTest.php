<?php

namespace Tests\Feature;

use App\Models\AuraScore;
use App\Models\Checkin;
use App\Models\Dimensao;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use App\Models\User;
use App\Services\GamificacaoService;
use App\Services\JornadaService;
use Database\Seeders\ConquistaSeeder;
use Database\Seeders\DimensaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GamificacaoTest extends TestCase
{
    use RefreshDatabase;

    private GamificacaoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DimensaoSeeder::class, ConquistaSeeder::class]);
        $this->service = new GamificacaoService;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_conceder_soma_xp_e_niveis_seguem_a_formula(): void
    {
        $user = User::factory()->create();

        $this->service->conceder($user, 'jornada_completa'); // +200

        $this->assertSame(200, $user->fresh()->xp);
        $this->assertSame(1, $this->service->nivel(99));
        $this->assertSame(2, $this->service->nivel(100));
        $this->assertSame(2, $this->service->nivel(399));
        $this->assertSame(3, $this->service->nivel(400));

        $progresso = $this->service->progressoNivel(200);
        $this->assertSame(2, $progresso['nivel']);
        $this->assertSame(400, $progresso['proximo_em']);
        $this->assertSame(33, $progresso['percentual']); // (200-100)/(400-100)
    }

    public function test_streak_conta_dias_consecutivos_e_quebra_em_lacunas(): void
    {
        Carbon::setTestNow('2026-08-25 12:00:00');
        $user = User::factory()->create();

        foreach ([0, 1, 2] as $diasAtras) {
            Checkin::factory()->for($user)->create(['created_at' => now()->subDays($diasAtras)]);
        }
        $this->assertSame(3, $this->service->streak($user));

        // lacuna: check-in de 5 dias atras nao estende a sequencia
        Checkin::factory()->for($user)->create(['created_at' => now()->subDays(5)]);
        $this->assertSame(3, $this->service->streak($user));
    }

    public function test_streak_terminando_ontem_ainda_conta(): void
    {
        Carbon::setTestNow('2026-08-25 12:00:00');
        $user = User::factory()->create();

        Checkin::factory()->for($user)->create(['created_at' => now()->subDay()]);
        Checkin::factory()->for($user)->create(['created_at' => now()->subDays(2)]);

        $this->assertSame(2, $this->service->streak($user));

        // dois dias sem check-in: zera
        Carbon::setTestNow('2026-08-27 12:00:00');
        $this->assertSame(0, $this->service->streak($user));
    }

    public function test_conquistas_sao_desbloqueadas_uma_unica_vez(): void
    {
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);

        Checkin::factory()->for($user)->create();
        $novas = $this->service->verificarConquistas($user);
        $this->assertContains('primeiro-passo', $novas);

        // repetir nao duplica
        $this->assertSame([], $this->service->verificarConquistas($user->fresh()));
        $this->assertSame(1, $user->conquistas()->count());
    }

    public function test_jornada_concluida_da_xp_e_conquista(): void
    {
        Carbon::setTestNow('2026-08-25 12:00:00');

        $user = User::factory()->create(['objetivo_principal' => 'prosperidade', 'tem_acesso' => true, 'onboarding_completo_em' => now()]);
        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'duracao_dias' => 1,
            'status' => 'publicado',
        ]);
        JornadaTemplateDia::factory()->create(['template_id' => $template->id, 'dia' => 1]);

        $jornadas = app(JornadaService::class);
        $jornada = $jornadas->criarParaUsuario($user);
        $jornadas->registrarCheckin($user, 5, null); // +5
        $jornadas->avancarSeCompleto($jornada->fresh()); // +20 +200

        $user = $user->fresh();
        $this->assertSame(225, $user->xp);
        $this->assertTrue($user->conquistas()->where('slug', 'circulo-completo')->exists());
        $this->assertTrue($user->conquistas()->where('slug', 'primeiro-passo')->exists());
    }

    public function test_acao_da_jornada_nao_duplica_xp_no_reenvio(): void
    {
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade', 'tem_acesso' => true, 'onboarding_completo_em' => now()]);
        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'duracao_dias' => 2,
            'status' => 'publicado',
        ]);
        $ritual = \App\Models\Audio::factory()->create(['tipo' => 'ritual']);
        // dia com ritual + acao: concluir so a acao NAO avanca o dia
        JornadaTemplateDia::factory()->create(['template_id' => $template->id, 'dia' => 1, 'ritual_audio_id' => $ritual->id]);
        JornadaTemplateDia::factory()->create(['template_id' => $template->id, 'dia' => 2, 'ritual_audio_id' => $ritual->id]);
        app(JornadaService::class)->criarParaUsuario($user);

        $this->actingAs($user)->post('/jornada/atividade', ['tipo' => 'acao']);
        $this->assertSame(GamificacaoService::XP['acao'], $user->fresh()->xp);

        // reenvio da MESMA atividade do MESMO dia: nada de XP extra
        $this->actingAs($user)->post('/jornada/atividade', ['tipo' => 'acao']);
        $this->assertSame(GamificacaoService::XP['acao'], $user->fresh()->xp);
    }

    public function test_renascimento_ao_ter_duas_medicoes(): void
    {
        $user = User::factory()->create();
        AuraScore::factory()->for($user)->count(2)->create();

        $this->assertContains('renascimento', $this->service->verificarConquistas($user));
    }
}
