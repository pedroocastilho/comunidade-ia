<?php

namespace App\Console\Commands;

use App\Services\CirculoMovimentoService;
use Illuminate\Console\Command;

/**
 * Um tique de movimento do Circulo (agendado de hora em hora em routes/console.php).
 *
 *   php artisan circulo:movimentar                  -> um tique, agora
 *   php artisan circulo:movimentar --simular-horas=72 -> reproduz as ultimas 72 horas
 *                                                       (preencher feed vazio, testar local)
 */
class CirculoMovimentar extends Command
{
    protected $signature = 'circulo:movimentar {--simular-horas=0 : Reproduz N horas passadas, uma a uma}';

    protected $description = 'Gera posts, curtidas e comentarios dos perfis ficticios do Circulo';

    public function handle(CirculoMovimentoService $servico): int
    {
        $horas = max(0, (int) $this->option('simular-horas'));
        $total = ['posts' => 0, 'curtidas' => 0, 'comentarios' => 0, 'respostas' => 0];

        for ($h = $horas; $h >= 0; $h--) {
            $resumo = $servico->executar(now()->subHours($h));
            foreach ($resumo as $chave => $n) {
                $total[$chave] += $n;
            }
        }

        $this->info(sprintf(
            'Circulo: %d post(s), %d curtida(s), %d comentario(s), %d resposta(s) em %d tique(s).',
            $total['posts'], $total['curtidas'], $total['comentarios'], $total['respostas'], $horas + 1,
        ));

        return self::SUCCESS;
    }
}
