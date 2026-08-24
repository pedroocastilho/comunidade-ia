<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_a_raiz_redireciona_visitante_para_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
