<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Aula;
use App\Models\Dimensao;
use App\Models\Jornada;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use App\Models\User;
use App\Services\JornadaService;
use Database\Seeders\DimensaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JornadaServiceTest extends TestCase
{
    use RefreshDatabase;

    private JornadaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DimensaoSeeder::class);
        $this->service = new JornadaService;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Cria um template publicado de $dias dias para a dimensao informada. */
    private function template(string $slug = 'prosperidade', int $dias = 3): JornadaTemplate
    {
        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', $slug)->value('id'),
            'duracao_dias' => $dias,
            'status' => 'publicado',
        ]);

        $audio = Audio::factory()->create();

        foreach (range(1, $dias) as $dia) {
            JornadaTemplateDia::factory()->create([
                'template_id' => $template->id,
                'dia' => $dia,
                'etapa' => 'Consciencia',
                'ritual_audio_id' => $audio->id,
                'acao_texto' => "Acao do dia {$dia}",
            ]);
        }

        return $template;
    }

    public function test_criar_para_usuario_materializa_os_dias_do_template(): void
    {
        $template = $this->template('prosperidade', 3);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);

        $jornada = $this->service->criarParaUsuario($user);

        $this->assertNotNull($jornada);
        $this->assertSame($template->id, $jornada->template_id);
        $this->assertSame(1, $jornada->dia_atual);
        $this->assertCount(3, $jornada->dias);
        $this->assertSame('Acao do dia 2', $jornada->dias[1]->acao_texto);
        $this->assertSame($template->dias[0]->ritual_audio_id, $jornada->dias[0]->ritual_audio_id);
    }

    public function test_criar_para_usuario_sem_template_publicado_retorna_null(): void
    {
        $user = User::factory()->create(['objetivo_principal' => 'proposito']);

        $this->assertNull($this->service->criarParaUsuario($user));
    }

    public function test_variante_curta_e_aplicada_para_tempo_5_10(): void
    {
        $template = $this->template('prosperidade', 1);
        $template->dias[0]->update(['variante_curta' => ['acao_texto' => 'Versao rapida']]);

        $userCurto = User::factory()->create(['objetivo_principal' => 'prosperidade', 'tempo_disponivel' => '5-10']);
        $userNormal = User::factory()->create(['objetivo_principal' => 'prosperidade', 'tempo_disponivel' => '30+']);

        $this->assertSame('Versao rapida', $this->service->criarParaUsuario($userCurto)->dias[0]->acao_texto);
        $this->assertSame('Acao do dia 1', $this->service->criarParaUsuario($userNormal)->dias[0]->acao_texto);
    }

    public function test_avanca_ao_concluir_as_tres_atividades(): void
    {
        $this->template('prosperidade', 3);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);
        $jornada = $this->service->criarParaUsuario($user);

        $dia = $this->service->diaAtual($jornada);
        $this->service->concluirAtividade($dia, 'ritual');
        $this->service->concluirAtividade($dia, 'aula');

        $this->assertFalse($this->service->avancarSeCompleto($jornada->fresh()));

        $this->service->concluirAtividade($dia->fresh(), 'acao');

        $this->assertTrue($this->service->avancarSeCompleto($jornada->fresh()));
        $this->assertSame(2, $jornada->fresh()->dia_atual);
        $this->assertNotNull($dia->fresh()->concluido_em);
    }

    public function test_dia_sem_aula_avanca_ao_concluir_as_atividades_existentes(): void
    {
        // template() cria dias com ritual + acao, sem aula: concluir os dois deve bastar
        $this->template('prosperidade', 3);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);
        $jornada = $this->service->criarParaUsuario($user);

        $dia = $this->service->diaAtual($jornada);
        $this->assertNull($dia->aula_id);

        $this->service->concluirAtividade($dia, 'ritual');
        $this->service->concluirAtividade($dia->fresh(), 'acao');

        $this->assertTrue($this->service->avancarSeCompleto($jornada->fresh()));
        $this->assertSame(2, $jornada->fresh()->dia_atual);
    }

    public function test_avanca_pelo_checkin_mesmo_sem_atividades(): void
    {
        $this->template('prosperidade', 3);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);
        $jornada = $this->service->criarParaUsuario($user);

        $this->service->registrarCheckin($user, 4, 'dia corrido');

        $this->assertTrue($this->service->avancarSeCompleto($jornada->fresh()));
        $this->assertSame(2, $jornada->fresh()->dia_atual);
    }

    public function test_no_maximo_um_avanco_por_dia_calendario(): void
    {
        Carbon::setTestNow('2026-08-25 10:00:00');
        $this->template('prosperidade', 3);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);
        $jornada = $this->service->criarParaUsuario($user);

        $this->service->registrarCheckin($user, 5, null);
        $this->assertTrue($this->service->avancarSeCompleto($jornada->fresh()));

        // concluir tudo do dia 2 ainda hoje nao avanca de novo
        $dia2 = $this->service->diaAtual($jornada->fresh());
        foreach (['ritual', 'aula', 'acao'] as $tipo) {
            $this->service->concluirAtividade($dia2, $tipo);
            $dia2 = $dia2->fresh();
        }
        $this->assertFalse($this->service->avancarSeCompleto($jornada->fresh()));
        $this->assertSame(2, $jornada->fresh()->dia_atual);

        // no dia-calendario seguinte, avanca
        Carbon::setTestNow('2026-08-26 10:00:00');
        $this->assertTrue($this->service->avancarSeCompleto($jornada->fresh()));
        $this->assertSame(3, $jornada->fresh()->dia_atual);
    }

    public function test_conclui_a_jornada_no_ultimo_dia(): void
    {
        Carbon::setTestNow('2026-08-25 10:00:00');
        $this->template('prosperidade', 1);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);
        $jornada = $this->service->criarParaUsuario($user);

        $this->service->registrarCheckin($user, 5, null);

        $this->assertTrue($this->service->avancarSeCompleto($jornada->fresh()));
        $jornada = $jornada->fresh();
        $this->assertSame('concluida', $jornada->status);
        $this->assertSame(1, $jornada->dia_atual);
    }

    public function test_checkin_e_unico_por_dia_calendario(): void
    {
        Carbon::setTestNow('2026-08-25 10:00:00');
        $this->template('prosperidade', 3);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);
        $this->service->criarParaUsuario($user);

        $primeiro = $this->service->registrarCheckin($user, 2, 'manha ruim');
        $segundo = $this->service->registrarCheckin($user, 4, 'melhorou');

        $this->assertSame($primeiro->id, $segundo->id);
        $this->assertSame(4, $segundo->humor);
        $this->assertSame(1, $user->checkins()->count());

        Carbon::setTestNow('2026-08-26 10:00:00');
        $this->service->registrarCheckin($user, 3, null);
        $this->assertSame(2, $user->checkins()->count());
    }

    public function test_concluir_aula_tambem_marca_atividade_da_jornada(): void
    {
        $this->template('prosperidade', 3);
        $user = User::factory()->create(['objetivo_principal' => 'prosperidade']);
        $jornada = $this->service->criarParaUsuario($user);

        $aula = Aula::factory()->create();
        $dia = $this->service->diaAtual($jornada);
        $dia->update(['aula_id' => $aula->id]);

        $this->service->concluirAulaDaJornada($user, $aula->id);

        $this->assertTrue($dia->fresh()->aula_concluida);
    }
}
