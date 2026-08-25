<?php

namespace Tests\Feature;

use App\Models\Audio;
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

class AudioWebTest extends TestCase
{
    use RefreshDatabase;

    private function aluno(): User
    {
        return User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);
    }

    public function test_lista_so_audios_publicados(): void
    {
        Audio::factory()->create(['titulo' => 'Frequencia 432Hz', 'status' => 'publicado']);
        Audio::factory()->create(['titulo' => 'Rascunho', 'status' => 'rascunho']);

        $this->actingAs($this->aluno())->get('/audios')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Audios')
                ->has('audios', 1)
                ->where('audios.0.titulo', 'Frequencia 432Hz'));
    }

    public function test_player_renderiza_e_registra_lesson_started(): void
    {
        $audio = Audio::factory()->create(['status' => 'publicado', 'arquivo_url' => 'https://cdn.exemplo/a.mp3']);
        $user = $this->aluno();

        $this->actingAs($user)->get("/audios/{$audio->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/AudioPlayer')
                ->where('audio.arquivo_url', 'https://cdn.exemplo/a.mp3'));

        $this->assertSame(1, EventoAnalytics::where('nome', 'lesson_started')
            ->where('user_id', $user->id)->count());
    }

    public function test_progresso_upserta_e_concluir_registra_evento(): void
    {
        $audio = Audio::factory()->create(['status' => 'publicado']);
        $user = $this->aluno();

        $this->actingAs($user)->post("/audios/{$audio->id}/progresso", ['posicao_segundos' => 30]);
        $this->actingAs($user)->post("/audios/{$audio->id}/progresso", ['posicao_segundos' => 90, 'concluido' => true]);

        $progresso = $user->progressoAudios()->where('audio_id', $audio->id)->first();
        $this->assertSame(90, $progresso->posicao_segundos);
        $this->assertTrue($progresso->concluido);
        $this->assertSame(1, $user->progressoAudios()->count());
        $this->assertSame(1, EventoAnalytics::where('nome', 'lesson_completed')
            ->where('user_id', $user->id)->count());
    }

    public function test_concluir_audio_do_ritual_marca_a_jornada(): void
    {
        $this->seed(DimensaoSeeder::class);

        $user = User::factory()->create([
            'tem_acesso' => true,
            'onboarding_completo_em' => now(),
            'objetivo_principal' => 'prosperidade',
        ]);
        $audio = Audio::factory()->create(['tipo' => 'ritual', 'status' => 'publicado']);

        $template = JornadaTemplate::factory()->create([
            'dimensao_id' => Dimensao::where('slug', 'prosperidade')->value('id'),
            'duracao_dias' => 1,
            'status' => 'publicado',
        ]);
        JornadaTemplateDia::factory()->create([
            'template_id' => $template->id,
            'dia' => 1,
            'ritual_audio_id' => $audio->id,
        ]);
        $jornada = app(JornadaService::class)->criarParaUsuario($user);

        $this->actingAs($user)->post("/audios/{$audio->id}/progresso", ['posicao_segundos' => 300, 'concluido' => true]);

        $this->assertTrue($jornada->fresh()->dias()->where('dia', 1)->first()->ritual_concluido);
    }
}
