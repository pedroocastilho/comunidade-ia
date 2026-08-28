<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostComentario;
use App\Models\PostReacao;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Movimento organico do Circulo: a cada "tique" de uma hora, perfis ficticios
 * publicam, curtem e comentam com distribuicao irregular (uns dias 1, outros 3,
 * outros nenhum) para o feed nunca parecer padronizado.
 *
 * Tetos por post e meta diaria de posts sao derivados de um hash estavel
 * (id do post / data), entao nao precisam de estado extra: cada execucao
 * so compara "o que ja existe" com "o teto sorteado" e avanca um pouco.
 */
class CirculoMovimentoService
{
    /** Horas do dia em que os perfis ficticios "estao acordados" (inclusive). */
    private const HORA_INICIO = 7;

    private const HORA_FIM = 23;

    /** Distribuicao da meta de posts ficticios por dia. */
    private const METAS_POSTS_DIA = [0, 0, 1, 1, 1, 2];

    /** Distribuicao do teto de comentarios ficticios por post (40% zero, 30% um...). */
    private const TETOS_COMENTARIOS = [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 2, 2, 2, 3, 3, 4];

    private array $frases;

    private ?Collection $perfis = null;

    public function __construct()
    {
        $this->frases = require database_path('data/circulo_frases.php');
    }

    /**
     * Executa um tique (uma hora) no instante informado.
     * Retorna o resumo do que foi criado.
     */
    public function executar(CarbonInterface $agora): array
    {
        $resumo = ['posts' => 0, 'curtidas' => 0, 'comentarios' => 0, 'respostas' => 0];

        if ($this->perfis()->isEmpty()) {
            return $resumo;
        }

        if ($agora->hour >= self::HORA_INICIO && $agora->hour <= self::HORA_FIM) {
            $resumo['posts'] += $this->publicarPostFicticio($agora);
        }

        $resumo['curtidas'] += $this->curtir($agora);
        [$comentarios, $respostas] = $this->comentar($agora);
        $resumo['comentarios'] += $comentarios;
        $resumo['respostas'] += $respostas;

        return $resumo;
    }

    // ------------------------------------------------------------ posts

    private function publicarPostFicticio(CarbonInterface $agora): int
    {
        $meta = $this->sortearDeterministico(self::METAS_POSTS_DIA, 'posts-'.$agora->toDateString());
        $publicadosHoje = Post::whereIn('user_id', $this->perfis()->pluck('id'))
            ->whereDate('created_at', $agora->toDateString())
            ->count();

        if ($publicadosHoje >= $meta) {
            return 0;
        }

        // Chance proporcional ao que falta, distribuida pelas horas "acordadas" restantes.
        $horasRestantes = max(1, self::HORA_FIM - $agora->hour + 1);
        $chance = ($meta - $publicadosHoje) / $horasRestantes;
        if (! $this->acaso($chance)) {
            return 0;
        }

        $autor = $this->perfis()->random();
        $dia = random_int(2, 29);
        $corpo = $this->variar($this->sortear($this->frases['posts']), ['{dia}' => $dia]);

        Post::create([
            'user_id' => $autor->id,
            'corpo' => $corpo,
            'status' => 'publicado',
            'created_at' => $this->instanteNaHora($agora, $agora->copy()->startOfHour()),
        ]);

        return 1;
    }

    // --------------------------------------------------------- curtidas

    private function curtir(CarbonInterface $agora): int
    {
        $criadas = 0;
        $posts = Post::where('status', 'publicado')
            ->where('created_at', '>=', $agora->copy()->subHours(48))
            ->where('created_at', '<=', $agora)
            ->get();

        foreach ($posts as $post) {
            $teto = 1 + $this->hash('curtidas-'.$post->id) % 6; // 1..6
            $existentes = PostReacao::where('post_id', $post->id)
                ->whereIn('user_id', $this->perfis()->pluck('id'))
                ->pluck('user_id');

            if ($existentes->count() >= $teto || ! $this->acaso(0.4)) {
                continue;
            }

            $candidatos = $this->perfis()
                ->reject(fn (User $u) => $u->id === $post->user_id || $existentes->contains($u->id))
                ->shuffle()
                ->take(min(random_int(1, 2), $teto - $existentes->count()));

            foreach ($candidatos as $perfil) {
                PostReacao::create([
                    'post_id' => $post->id,
                    'user_id' => $perfil->id,
                    'created_at' => $this->instanteNaHora($agora, $post->created_at),
                ]);
                $criadas++;
            }
        }

        return $criadas;
    }

    // ------------------------------------------------------ comentarios

    /** @return array{0:int,1:int} [comentarios, respostas] */
    private function comentar(CarbonInterface $agora): array
    {
        $comentarios = 0;
        $respostas = 0;

        $posts = Post::where('status', 'publicado')
            ->where('created_at', '>=', $agora->copy()->subHours(72))
            ->where('created_at', '<=', $agora)
            ->with('user:id,name,apelido,perfil_ficticio')
            ->get();

        foreach ($posts as $post) {
            $teto = $this->sortearDeterministico(self::TETOS_COMENTARIOS, 'comentarios-'.$post->id);
            $existentes = PostComentario::where('post_id', $post->id)
                ->whereIn('user_id', $this->perfis()->pluck('id'))
                ->get(['user_id', 'texto']);

            if ($existentes->count() >= $teto || ! $this->acaso(0.45)) {
                continue;
            }

            // Post de membro real: o primeiro comentario ficticio espera 20-90 minutos.
            $postReal = ! $post->user->perfil_ficticio;
            if ($postReal && $existentes->isEmpty()) {
                $espera = 20 + $this->hash('espera-'.$post->id) % 71;
                if ($post->created_at->copy()->addMinutes($espera)->gt($agora)) {
                    continue;
                }
            }

            $autor = $this->perfis()
                ->reject(fn (User $u) => $u->id === $post->user_id || $existentes->pluck('user_id')->contains($u->id))
                ->shuffle()
                ->first();
            if (! $autor) {
                continue;
            }

            $texto = $this->fraseInedita($this->frases['comentarios'], $existentes->pluck('texto'), $this->primeiroNome($post->user));
            $quando = $this->instanteNaHora($agora, $post->created_at);

            $comentario = PostComentario::create([
                'post_id' => $post->id,
                'user_id' => $autor->id,
                'texto' => $texto,
                'status' => 'publicado',
                'created_at' => $quando,
            ]);
            $comentarios++;

            // 30%: outro perfil responde curto logo depois (mesma lista, sem thread).
            if ($this->acaso(0.3)) {
                $respondente = $this->perfis()
                    ->reject(fn (User $u) => in_array($u->id, [$autor->id, $post->user_id], true))
                    ->shuffle()
                    ->first();
                if ($respondente) {
                    PostComentario::create([
                        'post_id' => $post->id,
                        'user_id' => $respondente->id,
                        'texto' => $this->fraseInedita($this->frases['respostas'], $existentes->pluck('texto')->push($texto), $this->primeiroNome($autor)),
                        'status' => 'publicado',
                        'created_at' => $quando->copy()->addMinutes(random_int(3, 40))->min($agora),
                    ]);
                    $respostas++;
                }
            }
        }

        return [$comentarios, $respostas];
    }

    // ---------------------------------------------------------- apoio

    private function perfis(): Collection
    {
        return $this->perfis ??= User::where('perfil_ficticio', true)->get(['id', 'name', 'apelido']);
    }

    private function primeiroNome(User $user): string
    {
        return explode(' ', trim($user->apelido ?: $user->name))[0];
    }

    /** Frase que ainda nao apareceu neste post (cai para qualquer uma se esgotar). */
    private function fraseInedita(array $banco, Collection $usadas, string $nome): string
    {
        $tentativas = 0;
        do {
            $texto = $this->variar($this->sortear($banco), ['{nome}' => $nome]);
            $tentativas++;
        } while ($usadas->contains($texto) && $tentativas < 12);

        return $texto;
    }

    /** Variacoes leves para nao repetir igual: marcadores, emoji opcional, pontuacao. */
    private function variar(string $texto, array $marcadores): string
    {
        $texto = strtr($texto, $marcadores);

        if ($this->acaso(0.35)) {
            $texto .= ' '.$this->sortear($this->frases['emojis']);
        } elseif ($this->acaso(0.25) && str_ends_with($texto, '.')) {
            $texto = substr($texto, 0, -1);
        }

        return $texto;
    }

    /** Instante aleatorio entre o inicio da hora corrente e "agora", nunca antes do minimo (o post). */
    private function instanteNaHora(CarbonInterface $agora, CarbonInterface $minimo): CarbonInterface
    {
        $inicio = $agora->copy()->startOfHour()->max($minimo)->min($agora);
        $fim = $agora->copy();
        $intervalo = max(0, $fim->diffInSeconds($inicio, true));

        return $inicio->copy()->addSeconds($intervalo > 0 ? random_int(0, (int) $intervalo) : 0);
    }

    private function sortear(array $lista): mixed
    {
        return $lista[array_rand($lista)];
    }

    /** Escolha estavel de uma lista a partir de uma chave (mesma chave, mesmo resultado). */
    private function sortearDeterministico(array $lista, string $chave): mixed
    {
        return $lista[$this->hash($chave) % count($lista)];
    }

    private function hash(string $chave): int
    {
        return crc32(config('app.key').'|'.$chave);
    }

    private function acaso(float $chance): bool
    {
        return random_int(1, 10000) <= (int) round($chance * 10000);
    }
}
