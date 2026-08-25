<?php

namespace Tests\Feature;

use App\Models\Dimensao;
use App\Models\IaConfiguracao;
use App\Models\QuestionarioPergunta;
use Database\Seeders\DimensaoSeeder;
use Database\Seeders\IaConfiguracaoSeeder;
use Database\Seeders\QuestionarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuraSeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DimensaoSeeder::class, QuestionarioSeeder::class, IaConfiguracaoSeeder::class]);
    }

    public function test_seeds_criam_5_dimensoes_com_slugs_fixos(): void
    {
        $this->assertSame(
            ['prosperidade', 'relacionamentos', 'saude-energia', 'proposito', 'mentalidade'],
            Dimensao::orderBy('ordem')->pluck('slug')->all()
        );
    }

    public function test_seeds_criam_as_12_perguntas_do_onboarding(): void
    {
        $this->assertSame(12, QuestionarioPergunta::count());

        $p2 = QuestionarioPergunta::where('ordem', 2)->first();
        $this->assertSame('escolha_unica', $p2->tipo);
        $this->assertCount(5, $p2->opcoes);
        $this->assertTrue($p2->obrigatoria);
        $this->assertTrue($p2->alimenta_memoria);

        foreach ([4, 5, 6, 7, 8, 9] as $ordem) {
            $pergunta = QuestionarioPergunta::where('ordem', $ordem)->first();
            $this->assertSame('escala', $pergunta->tipo, "Pergunta {$ordem} deveria ser escala");
            $this->assertTrue($pergunta->obrigatoria);
            $this->assertNotNull($pergunta->dimensao_id, "Pergunta {$ordem} precisa de dimensao");
        }

        $p12 = QuestionarioPergunta::where('ordem', 12)->first();
        $this->assertFalse($p12->obrigatoria);
        $this->assertTrue($p12->alimenta_memoria);
    }

    public function test_seeds_criam_configuracoes_da_ia(): void
    {
        $this->assertSame('claude-haiku-4-5-20251001', IaConfiguracao::valor('modelo'));
        $this->assertNotNull(IaConfiguracao::valor('system_prompt'));
        $this->assertNotNull(IaConfiguracao::valor('temperatura'));
        $this->assertNotNull(IaConfiguracao::valor('max_tokens'));
        $this->assertNotNull(IaConfiguracao::valor('teto_diario_tokens'));
    }

    public function test_seeds_sao_idempotentes(): void
    {
        $this->seed([DimensaoSeeder::class, QuestionarioSeeder::class, IaConfiguracaoSeeder::class]);

        $this->assertSame(5, Dimensao::count());
        $this->assertSame(12, QuestionarioPergunta::count());
        $this->assertSame(5, IaConfiguracao::count());
    }

    public function test_seed_cria_as_5_jornadas_oficiais_com_30_dias(): void
    {
        $this->seed(\Database\Seeders\JornadaTemplateSeeder::class);
        $this->seed(\Database\Seeders\JornadaTemplateSeeder::class); // idempotente

        $templates = \App\Models\JornadaTemplate::with('dias')->get();
        $this->assertCount(5, $templates);

        foreach ($templates as $template) {
            $this->assertSame('publicado', $template->status);
            $this->assertCount(30, $template->dias);
            $this->assertSame(30, $template->dias->pluck('acao_texto')->filter()->count());
            $this->assertCount(4, $template->dias->pluck('etapa')->unique());
        }

        // cada dimensao tem exatamente 1 template
        $this->assertSame(5, $templates->pluck('dimensao_id')->unique()->count());
    }
}
