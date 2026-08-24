<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_valido_retorna_token(): void
    {
        User::factory()->create(['email' => 'a@a.com', 'password' => 'senha12345']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'a@a.com',
            'password' => 'senha12345',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_invalido_retorna_401(): void
    {
        User::factory()->create(['email' => 'a@a.com', 'password' => 'senha12345']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'a@a.com',
            'password' => 'errada',
        ])->assertUnauthorized();
    }

    public function test_logout_revoga_o_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/auth/logout')->assertNoContent();
    }
}
