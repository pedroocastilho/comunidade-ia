<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\BoasVindasAssinante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_boas_vindas_renderiza_com_identidade_do_circulo(): void
    {
        $user = User::factory()->create(['name' => 'Maria Silva']);

        $html = (string) (new BoasVindasAssinante)->toMail($user)->render();

        $this->assertStringContainsString('logo-email.png', $html);
        $this->assertStringContainsString('CÍRCULO AURA', $html);
        $this->assertStringContainsString('#C9A24B', $html); // dourado da marca
        $this->assertStringContainsString('Olá, Maria!', $html);
        $this->assertStringContainsString('12345678', $html);
        $this->assertStringContainsString('Você recebeu este e-mail porque tem uma conta', $html);
    }
}
