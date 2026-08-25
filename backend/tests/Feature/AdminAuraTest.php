<?php

namespace Tests\Feature;

use App\Filament\Resources\Audio\AudioResource;
use App\Filament\Resources\AuraConversas\AuraConversaResource;
use App\Filament\Resources\IaConfiguracaos\IaConfiguracaoResource;
use App\Filament\Resources\JornadaTemplates\JornadaTemplateResource;
use App\Filament\Resources\QuestionarioPerguntas\QuestionarioPerguntaResource;
use App\Filament\Resources\WebhookPagamentos\WebhookPagamentoResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuraTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_paginas_dos_novos_resources_respondem_para_admin(): void
    {
        $admin = $this->admin();

        foreach ([
            AudioResource::class,
            JornadaTemplateResource::class,
            QuestionarioPerguntaResource::class,
            IaConfiguracaoResource::class,
            WebhookPagamentoResource::class,
            AuraConversaResource::class,
        ] as $resource) {
            $this->actingAs($admin)
                ->get($resource::getUrl('index'))
                ->assertOk();
        }
    }

    public function test_aluno_nao_acessa_o_admin(): void
    {
        $aluno = User::factory()->create(['role' => 'aluno']);

        $this->actingAs($aluno)->get(AudioResource::getUrl('index'))->assertForbidden();
    }

    public function test_recursos_de_leitura_nao_permitem_criar(): void
    {
        $this->assertFalse(WebhookPagamentoResource::canCreate());
        $this->assertFalse(AuraConversaResource::canCreate());
        $this->assertFalse(IaConfiguracaoResource::canCreate());
    }
}
