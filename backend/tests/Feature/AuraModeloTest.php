<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\AuraConversa;
use App\Models\AuraMemoria;
use App\Models\AuraMensagem;
use App\Models\AuraScore;
use App\Models\Checkin;
use App\Models\Dimensao;
use App\Models\IaConfiguracao;
use App\Models\Jornada;
use App\Models\JornadaDia;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use App\Models\ProgressoAudio;
use App\Models\QuestionarioPergunta;
use App\Models\QuestionarioResposta;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuraModeloTest extends TestCase
{
    use RefreshDatabase;

    public function test_grafo_completo_do_dominio_aura(): void
    {
        $user = User::factory()->create();

        $dimensao = Dimensao::create(['nome' => 'Prosperidade', 'slug' => 'prosperidade', 'ordem' => 1]);

        $pergunta = QuestionarioPergunta::factory()->create(['dimensao_id' => $dimensao->id]);
        QuestionarioResposta::create(['user_id' => $user->id, 'pergunta_id' => $pergunta->id, 'valor' => '7']);

        $score = AuraScore::factory()->for($user)->create(['dimensao_prioritaria_id' => $dimensao->id]);

        $template = JornadaTemplate::factory()->create(['dimensao_id' => $dimensao->id]);
        $audio = Audio::factory()->create();
        JornadaTemplateDia::factory()->for($template, 'template')->create(['ritual_audio_id' => $audio->id]);

        $jornada = Jornada::factory()->for($user)->create(['template_id' => $template->id]);
        $dia = JornadaDia::factory()->for($jornada)->create(['ritual_audio_id' => $audio->id]);

        $checkin = Checkin::factory()->for($user)->create(['jornada_dia_id' => $dia->id]);

        $conversa = AuraConversa::factory()->for($user)->create();
        AuraMensagem::factory()->for($conversa, 'conversa')->create();
        AuraMemoria::factory()->for($user)->create();

        ProgressoAudio::create(['user_id' => $user->id, 'audio_id' => $audio->id, 'posicao_segundos' => 30]);

        IaConfiguracao::create(['chave' => 'modelo', 'valor' => 'claude-haiku-4-5-20251001']);

        $this->assertSame($dimensao->id, $score->dimensaoPrioritaria->id);
        $this->assertSame($user->id, $user->auraScores->first()->user->id);
        $this->assertSame($template->id, $jornada->template->id);
        $this->assertSame($dia->id, $jornada->dias->first()->id);
        $this->assertSame($audio->id, $dia->ritualAudio->id);
        $this->assertSame($dia->id, $checkin->jornadaDia->id);
        $this->assertCount(1, $conversa->mensagens);
        $this->assertCount(1, $user->auraMemorias);
        $this->assertSame($jornada->id, $user->jornadaAtiva->id);
        $this->assertSame('claude-haiku-4-5-20251001', IaConfiguracao::valor('modelo'));
        $this->assertNull(IaConfiguracao::valor('inexistente'));
        $this->assertSame('padrao', IaConfiguracao::valor('inexistente', 'padrao'));
    }

    public function test_resposta_de_questionario_e_unica_por_usuario_e_pergunta(): void
    {
        $user = User::factory()->create();
        $pergunta = QuestionarioPergunta::factory()->create();

        QuestionarioResposta::create(['user_id' => $user->id, 'pergunta_id' => $pergunta->id, 'valor' => '5']);

        $this->expectException(QueryException::class);
        QuestionarioResposta::create(['user_id' => $user->id, 'pergunta_id' => $pergunta->id, 'valor' => '8']);
    }

    public function test_progresso_de_audio_e_unico_por_usuario_e_audio(): void
    {
        $user = User::factory()->create();
        $audio = Audio::factory()->create();

        ProgressoAudio::create(['user_id' => $user->id, 'audio_id' => $audio->id]);

        $this->expectException(QueryException::class);
        ProgressoAudio::create(['user_id' => $user->id, 'audio_id' => $audio->id]);
    }

    public function test_usuario_novo_tem_assinatura_status_manual(): void
    {
        $user = User::factory()->create();

        $this->assertSame('manual', $user->fresh()->assinatura_status);
    }
}
