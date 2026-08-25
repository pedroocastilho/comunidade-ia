<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\AuraConversa;
use App\Models\Curso;
use App\Models\Dimensao;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use App\Models\User;
use App\Services\AuraChatService;
use App\Services\AuraGuardrails;
use App\Services\AuraScoreService;
use App\Services\JornadaService;
use Database\Seeders\DimensaoSeeder;
use Database\Seeders\IaConfiguracaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuraChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => 'chave-teste']);
        $this->seed([DimensaoSeeder::class, IaConfiguracaoSeeder::class]);
    }

    /** Usuario completo: score + jornada de 3 dias com equivalentes no dia 2. */
    private function usuarioCompleto(): array
    {
        $user = User::factory()->create([
            'tem_acesso' => true,
            'onboarding_completo_em' => now(),
            'apelido' => 'Chico',
            'objetivo_principal' => 'prosperidade',
        ]);

        $resultado = app(AuraScoreService::class)->calcular(
            ['p4' => 3, 'p5' => 7, 'p6' => 6, 'p7' => 5, 'p8' => 6, 'p9' => 8],
            'prosperidade',
            null,
        );
        app(AuraScoreService::class)->salvar($user, $resultado);

        $user->auraMemorias()->create([
            'tipo' => 'contexto', 'conteudo' => 'Quer viver de renda passiva.', 'origem' => 'questionario', 'ativo' => true,
        ]);

        $ritualA = Audio::factory()->create(['tipo' => 'ritual', 'titulo' => 'Ritual A', 'status' => 'publicado']);
        $ritualB = Audio::factory()->create(['tipo' => 'ritual', 'titulo' => 'Ritual B', 'status' => 'publicado']);

        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'duracao_dias' => 3,
            'status' => 'publicado',
        ]);
        foreach (range(1, 3) as $dia) {
            JornadaTemplateDia::factory()->create([
                'template_id' => $template->id,
                'dia' => $dia,
                'ritual_audio_id' => $ritualA->id,
                'acao_texto' => 'Acao original',
                'equivalentes' => ['ritual' => [$ritualB->id], 'acao' => ['Acao mais leve']],
            ]);
        }
        $jornada = app(JornadaService::class)->criarParaUsuario($user);

        return [$user, $jornada, $ritualA, $ritualB];
    }

    private function respostaTexto(string $texto): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $texto]],
            'stop_reason' => 'end_turn',
            'usage' => ['input_tokens' => 100, 'output_tokens' => 20],
        ];
    }

    public function test_contexto_contem_perfil_score_jornada_e_memorias(): void
    {
        [$user] = $this->usuarioCompleto();

        $contexto = app(AuraChatService::class)->montarContexto($user->fresh());

        $this->assertStringContainsString('Chico', $contexto);
        $this->assertStringContainsString('52', $contexto); // score global: (30*2+70+60+50+70)/6
        $this->assertStringContainsString('Dia 1 de 3', $contexto);
        $this->assertStringContainsString('renda passiva', $contexto);
    }

    public function test_crise_responde_cvv_sem_chamar_a_api(): void
    {
        Http::fake();
        [$user] = $this->usuarioCompleto();
        $conversa = AuraConversa::factory()->for($user)->create();

        $resposta = app(AuraChatService::class)->enviar($user, $conversa, 'nao aguento mais, penso em me matar');

        $this->assertStringContainsString('188', $resposta->conteudo);
        $this->assertSame('crise', $conversa->fresh()->classificacao);
        Http::assertNothingSent();
    }

    public function test_conversa_normal_persiste_mensagens_com_tokens(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->respostaTexto('Que bom te ver por aqui, Chico. ✦'))]);
        [$user] = $this->usuarioCompleto();
        $conversa = AuraConversa::factory()->for($user)->create();

        $resposta = app(AuraChatService::class)->enviar($user, $conversa, 'oi Aura');

        $this->assertSame(2, $conversa->mensagens()->count());
        $this->assertSame('assistant', $resposta->papel);
        $this->assertSame(120, $resposta->tokens);
        $this->assertStringContainsString('Chico', $resposta->conteudo);
    }

    public function test_loop_de_ferramenta_buscar_conteudo(): void
    {
        Curso::factory()->create(['titulo' => 'Prosperidade do Zero', 'status' => 'publicado']);

        Http::fakeSequence('api.anthropic.com/*')
            ->push([
                'content' => [
                    ['type' => 'text', 'text' => 'Deixa eu ver...'],
                    ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'buscar_conteudo', 'input' => ['termo' => 'prosperidade']],
                ],
                'stop_reason' => 'tool_use',
                'usage' => ['input_tokens' => 50, 'output_tokens' => 10],
            ])
            ->push($this->respostaTexto('Recomendo o curso Prosperidade do Zero.'));

        [$user] = $this->usuarioCompleto();
        $conversa = AuraConversa::factory()->for($user)->create();

        $resposta = app(AuraChatService::class)->enviar($user, $conversa, 'tem algo sobre dinheiro?');

        $this->assertStringContainsString('Prosperidade do Zero', $resposta->conteudo);
        Http::assertSentCount(2);
    }

    public function test_adaptacao_valida_e_aplicada_com_log(): void
    {
        [$user, $jornada, , $ritualB] = $this->usuarioCompleto();
        $conversa = AuraConversa::factory()->for($user)->create();

        Http::fakeSequence('api.anthropic.com/*')
            ->push([
                'content' => [
                    ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'sugerir_adaptacao', 'input' => [
                        'dia' => 2, 'atividade' => 'ritual', 'substituto_id' => $ritualB->id, 'motivo' => 'usuario cansado',
                    ]],
                ],
                'stop_reason' => 'tool_use',
                'usage' => ['input_tokens' => 50, 'output_tokens' => 10],
            ])
            ->push($this->respostaTexto('Troquei o ritual de amanha por um mais leve.'));

        app(AuraChatService::class)->enviar($user, $conversa, 'to muito cansado pra amanha');

        $dia2 = $jornada->fresh()->dias()->where('dia', 2)->first();
        $this->assertSame($ritualB->id, $dia2->ritual_audio_id);
        $this->assertTrue($dia2->adaptado_por_ia);
        $this->assertSame('usuario cansado', $dia2->origem_adaptacao['motivo']);
    }

    public function test_adaptacao_invalida_e_rejeitada_sem_mudar_nada(): void
    {
        [$user, $jornada, $ritualA] = $this->usuarioCompleto();
        $intruso = Audio::factory()->create(['tipo' => 'ritual']); // fora do pool de equivalentes

        $guardrails = app(AuraGuardrails::class);

        // substituto fora do pool
        $this->assertFalse($guardrails->validarAdaptacao($user, 2, 'ritual', $intruso->id));
        // dia corrente (ja iniciado) e dia passado
        $this->assertFalse($guardrails->validarAdaptacao($user, 1, 'ritual', $ritualA->id));
        $this->assertFalse($guardrails->validarAdaptacao($user, 0, 'ritual', $ritualA->id));
        // tipo sem pool
        $this->assertFalse($guardrails->validarAdaptacao($user, 2, 'aula', 999));

        $dia2 = $jornada->fresh()->dias()->where('dia', 2)->first();
        $this->assertFalse($dia2->adaptado_por_ia);
        $this->assertSame($ritualA->id, $dia2->ritual_audio_id);
    }

    public function test_detector_de_crise(): void
    {
        $service = app(AuraChatService::class);

        $this->assertTrue($service->detectarCrise('quero me matar'));
        $this->assertTrue($service->detectarCrise('ando pensando em suicidio'));
        $this->assertTrue($service->detectarCrise('venho me machucando de proposito'));
        $this->assertFalse($service->detectarCrise('quero matar a saudade da minha familia'));
        $this->assertFalse($service->detectarCrise('como manifesto prosperidade?'));
    }
}
