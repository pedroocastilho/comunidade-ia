<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostComentario;
use App\Models\PostReacao;
use App\Models\User;
use App\Services\CirculoMovimentoService;
use Database\Seeders\PerfisFicticiosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CirculoMovimentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerfisFicticiosSeeder::class);
    }

    public function test_seeder_cria_perfis_ficticios_sem_acesso_e_idempotente(): void
    {
        $this->assertSame(40, User::where('perfil_ficticio', true)->count());
        $this->assertSame(0, User::where('perfil_ficticio', true)->where('tem_acesso', true)->count());

        $this->seed(PerfisFicticiosSeeder::class);
        $this->assertSame(40, User::where('perfil_ficticio', true)->count());
    }

    public function test_simulacao_gera_movimento_apenas_com_perfis_ficticios(): void
    {
        $real = User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);
        $post = Post::factory()->for($real)->create(['created_at' => now()->subHours(30)]);

        $this->artisan('circulo:movimentar', ['--simular-horas' => 96])->assertSuccessful();

        $ficticios = User::where('perfil_ficticio', true)->pluck('id');

        // Ha movimento: posts ficticios e curtidas/comentarios no post real
        $this->assertGreaterThan(0, Post::whereIn('user_id', $ficticios)->count());
        $this->assertGreaterThan(0, PostReacao::where('post_id', $post->id)->count());
        $this->assertGreaterThan(0, PostComentario::count());

        // Tudo que foi gerado veio de perfis ficticios
        $this->assertSame(0, Post::whereNotIn('user_id', $ficticios)->where('id', '!=', $post->id)->count());
        $this->assertSame(0, PostReacao::whereNotIn('user_id', $ficticios)->count());
        $this->assertSame(0, PostComentario::whereNotIn('user_id', $ficticios)->count());

        // Nunca curte duas vezes o mesmo post nem comenta o proprio post
        $duplicadas = PostReacao::selectRaw('post_id, user_id, count(*) as n')
            ->groupBy('post_id', 'user_id')->having('n', '>', 1)->count();
        $this->assertSame(0, $duplicadas);
        $autoComentarios = PostComentario::join('posts', 'posts.id', '=', 'post_comentarios.post_id')
            ->whereColumn('posts.user_id', 'post_comentarios.user_id')->count();
        $this->assertSame(0, $autoComentarios);

        // Tetos: no maximo 6 curtidas ficticias e comentarios (com respostas) razoaveis por post
        $maxCurtidas = PostReacao::selectRaw('post_id, count(*) as n')->groupBy('post_id')->get()->max('n');
        $this->assertLessThanOrEqual(6, $maxCurtidas);

        // Conteudo nasce publicado e com data no passado (nunca no futuro)
        $this->assertSame(0, Post::where('status', '!=', 'publicado')->count());
        $this->assertSame(0, Post::where('created_at', '>', now())->count());
        $this->assertSame(0, PostComentario::where('created_at', '>', now())->count());
    }

    public function test_post_real_espera_antes_do_primeiro_comentario_ficticio(): void
    {
        $real = User::factory()->create();
        $post = Post::factory()->for($real)->create(['created_at' => now()->subMinutes(5)]);

        $servico = app(CirculoMovimentoService::class);
        for ($i = 0; $i < 30; $i++) {
            $servico->executar(now());
        }

        $this->assertSame(0, PostComentario::where('post_id', $post->id)->count());
    }

    public function test_post_real_so_recebe_comentarios_genericos_e_genero_bate_com_o_perfil(): void
    {
        $real = User::factory()->create();
        $post = Post::factory()->for($real)->create(['corpo' => 'Meu marido comecou a jornada comigo.', 'created_at' => now()->subHours(40)]);

        $this->artisan('circulo:movimentar', ['--simular-horas' => 120])->assertSuccessful();

        $frases = require database_path('data/circulo_frases.php');
        $especificas = collect(array_merge($frases['comentarios'], $frases['respostas']))
            ->filter(fn ($f) => $f['tema'] !== null)->pluck('t');
        $femininas = collect(array_merge($frases['comentarios'], $frases['respostas']))
            ->filter(fn ($f) => $f['g'] === 'f')->pluck('t');
        $masculinas = collect(array_merge($frases['comentarios'], $frases['respostas']))
            ->filter(fn ($f) => $f['g'] === 'm')->pluck('t');

        $comentarios = PostComentario::where('post_id', $post->id)->with('user:id,genero')->get();
        $this->assertGreaterThan(0, $comentarios->count());

        $comeca = fn ($texto, $modelo) => str_starts_with($texto, rtrim($modelo, '.'));
        foreach ($comentarios as $comentario) {
            $this->assertFalse($especificas->contains(fn ($m) => $comeca($comentario->texto, $m)), "Especifica em post real: {$comentario->texto}");
            if ($comentario->user->genero === 'm') {
                $this->assertFalse($femininas->contains(fn ($m) => $comeca($comentario->texto, $m)), "Feminina em perfil masculino: {$comentario->texto}");
            } else {
                $this->assertFalse($masculinas->contains(fn ($m) => $comeca($comentario->texto, $m)), "Masculina em perfil feminino: {$comentario->texto}");
            }
        }

        // Posts ficticios com texto no feminino saem so de perfis femininos
        $postsF = collect($frases['posts'])->filter(fn ($p) => $p['g'] === 'f')->pluck('t');
        $ficticios = Post::where('id', '!=', $post->id)->with('user:id,genero')->get();
        foreach ($ficticios as $p) {
            if ($postsF->contains(fn ($m) => str_starts_with($p->corpo, mb_substr($m, 0, 40)))) {
                $this->assertSame('f', $p->user->genero, "Post feminino em perfil masculino: {$p->corpo}");
            }
        }
    }

    public function test_sem_perfis_ficticios_nao_faz_nada(): void
    {
        User::where('perfil_ficticio', true)->delete();
        Post::factory()->create();

        $this->artisan('circulo:movimentar', ['--simular-horas' => 24])->assertSuccessful();

        $this->assertSame(1, Post::count());
        $this->assertSame(0, PostReacao::count());
        $this->assertSame(0, PostComentario::count());
    }

    public function test_metricas_do_admin_ignoram_ficticios(): void
    {
        $this->assertSame(0, User::where('perfil_ficticio', false)->whereNotNull('onboarding_completo_em')->count());
        $this->assertSame(40, User::whereNotNull('onboarding_completo_em')->count());
    }
}
