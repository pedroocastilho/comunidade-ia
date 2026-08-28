<?php

namespace Tests\Feature;

use App\Models\Meta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MetasTest extends TestCase
{
    use RefreshDatabase;

    private function aluno(): User
    {
        return User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);
    }

    public function test_pagina_lista_metas_com_situacao(): void
    {
        $user = $this->aluno();
        Meta::factory()->for($user)->create(['titulo' => 'Meditar', 'prazo' => now()->addDays(3)]);
        Meta::factory()->for($user)->create(['titulo' => 'Ler', 'prazo' => now()->subDay()]);
        Meta::factory()->for($user)->create(['titulo' => 'Correr', 'prazo' => now()->subDays(2), 'concluida_em' => now()]);
        Meta::factory()->create(['titulo' => 'De outra pessoa']);

        $this->actingAs($user)->get('/metas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Metas')
                ->has('metas', 3)
                ->where('metas.0.titulo', 'Correr')
                ->where('metas.0.situacao', 'concluida')
                ->where('metas.1.situacao', 'vencida')
                ->where('metas.2.situacao', 'andamento'));
    }

    public function test_cria_marca_e_exclui_meta(): void
    {
        $user = $this->aluno();

        $this->actingAs($user)->post('/metas', ['titulo' => 'Acordar 6h', 'prazo' => now()->addDays(5)->toDateString()])
            ->assertRedirect();
        $meta = Meta::first();
        $this->assertSame($user->id, $meta->user_id);
        $this->assertNull($meta->concluida_em);

        $this->actingAs($user)->patch("/metas/{$meta->id}", ['concluida' => true])->assertRedirect();
        $this->assertNotNull($meta->fresh()->concluida_em);

        $this->actingAs($user)->patch("/metas/{$meta->id}", ['concluida' => false])->assertRedirect();
        $this->assertNull($meta->fresh()->concluida_em);

        $this->actingAs($user)->delete("/metas/{$meta->id}")->assertRedirect();
        $this->assertSame(0, Meta::count());
    }

    public function test_validacao_de_prazo_e_titulo(): void
    {
        $user = $this->aluno();

        $this->actingAs($user)->from('/metas')
            ->post('/metas', ['titulo' => 'x', 'prazo' => now()->subDay()->toDateString()])
            ->assertSessionHasErrors(['titulo', 'prazo']);
        $this->assertSame(0, Meta::count());
    }

    public function test_nao_mexe_em_meta_de_outro_usuario(): void
    {
        $user = $this->aluno();
        $alheia = Meta::factory()->create();

        $this->actingAs($user)->patch("/metas/{$alheia->id}", ['concluida' => true])->assertForbidden();
        $this->actingAs($user)->delete("/metas/{$alheia->id}")->assertForbidden();
        $this->assertSame(1, Meta::count());
    }

    public function test_home_traz_metas_proximas_em_andamento(): void
    {
        $user = $this->aluno();
        Meta::factory()->for($user)->create(['titulo' => 'Longe', 'prazo' => now()->addDays(20)]);
        Meta::factory()->for($user)->create(['titulo' => 'Perto', 'prazo' => now()->addDays(1)]);
        Meta::factory()->for($user)->create(['titulo' => 'Feita', 'prazo' => now()->addDays(1), 'concluida_em' => now()]);
        Meta::factory()->for($user)->create(['titulo' => 'Vencida', 'prazo' => now()->subDay()]);

        $this->actingAs($user)->get('/inicio')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('metas_proximas', 2)
                ->where('metas_proximas.0.titulo', 'Perto'));
    }
}
