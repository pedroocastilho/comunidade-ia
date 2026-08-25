<?php

namespace Tests\Unit;

use App\Models\EventoAnalytics;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_evento_com_usuario_e_propriedades(): void
    {
        $user = User::factory()->create();

        (new AnalyticsService)->registrar('onboarding_completed', $user, ['objetivo' => 'prosperidade']);

        $evento = EventoAnalytics::first();
        $this->assertSame('onboarding_completed', $evento->nome);
        $this->assertSame($user->id, $evento->user_id);
        $this->assertSame('prosperidade', $evento->propriedades['objetivo']);
    }

    public function test_registra_evento_anonimo_sem_propriedades(): void
    {
        (new AnalyticsService)->registrar('onboarding_started');

        $evento = EventoAnalytics::first();
        $this->assertNull($evento->user_id);
        $this->assertNull($evento->propriedades);
    }

    public function test_nome_invalido_lanca_excecao_em_ambiente_de_teste(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new AnalyticsService)->registrar('evento_que_nao_existe');
    }
}
