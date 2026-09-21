<?php

namespace Tests\Unit;

use App\Services\AuraScoreService;
use PHPUnit\Framework\TestCase;

class AuraScoreServiceTest extends TestCase
{
    private AuraScoreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuraScoreService;
    }

    /** Escalas p4..p9 todas com o mesmo valor. */
    private function escalas(int $valor): array
    {
        return ['p4' => $valor, 'p5' => $valor, 'p6' => $valor, 'p7' => $valor, 'p8' => $valor, 'p9' => $valor];
    }

    public function test_tudo_10_gera_score_100_e_padrao_pronto_para_acelerar(): void
    {
        $resultado = $this->service->calcular($this->escalas(10), 'prosperidade', null);

        $this->assertSame(100, $resultado['score_global']);
        $this->assertSame(100, $resultado['scores_dimensoes']['prosperidade']);
        $this->assertSame(100, $resultado['scores_dimensoes']['mentalidade']);
        $this->assertSame(['pronto_para_acelerar'], $resultado['padroes']);
        $this->assertSame('prosperidade', $resultado['dimensao_prioritaria']);
    }

    public function test_tudo_0_gera_reconstrucao_e_bloqueio_crenca(): void
    {
        $resultado = $this->service->calcular($this->escalas(0), 'prosperidade', null);

        $this->assertSame(0, $resultado['score_global']);
        $this->assertSame(['reconstrucao', 'bloqueio_crenca'], $resultado['padroes']);
    }

    public function test_mentalidade_e_media_entre_p8_e_p9(): void
    {
        $escalas = ['p4' => 5, 'p5' => 5, 'p6' => 5, 'p7' => 5, 'p8' => 4, 'p9' => 9];

        $resultado = $this->service->calcular($escalas, 'prosperidade', null);

        // (4*10 + 9*10) / 2 = 65
        $this->assertSame(65, $resultado['scores_dimensoes']['mentalidade']);
    }

    public function test_pesos_do_objetivo_principal_e_secundario(): void
    {
        // prosperidade 100 (peso 2), relacionamentos 0 (peso 1.5), demais 0 (peso 1)
        $escalas = ['p4' => 10, 'p5' => 0, 'p6' => 0, 'p7' => 0, 'p8' => 0, 'p9' => 0];

        $comSecundario = $this->service->calcular($escalas, 'prosperidade', 'relacionamentos');
        // (100*2 + 0*1.5 + 0 + 0 + 0) / 6.5 = 30.77 -> 31
        $this->assertSame(31, $comSecundario['score_global']);

        $semSecundario = $this->service->calcular($escalas, 'prosperidade', null);
        // (100*2 + 0 + 0 + 0 + 0) / 6 = 33.33 -> 33
        $this->assertSame(33, $semSecundario['score_global']);
    }

    public function test_desequilibrio_quando_gap_maior_que_30(): void
    {
        $escalas = ['p4' => 10, 'p5' => 5, 'p6' => 5, 'p7' => 5, 'p8' => 5, 'p9' => 5];

        $resultado = $this->service->calcular($escalas, 'prosperidade', null);

        $this->assertSame(['desequilibrio'], $resultado['padroes']);
    }

    public function test_padroes_acumulam_no_maximo_2_na_ordem_de_avaliacao(): void
    {
        // global < 40, gap > 30 e crenca <= 4: os 2 primeiros vencem
        $escalas = ['p4' => 7, 'p5' => 0, 'p6' => 0, 'p7' => 0, 'p8' => 3, 'p9' => 3];

        $resultado = $this->service->calcular($escalas, 'relacionamentos', null);

        $this->assertSame(['reconstrucao', 'desequilibrio'], $resultado['padroes']);
    }

    public function test_base_solida_quando_nenhum_padrao_dispara(): void
    {
        // scores medios, sem gap, crenca ok mas global < 70
        $resultado = $this->service->calcular($this->escalas(6), 'proposito', null);

        $this->assertSame(['base_solida'], $resultado['padroes']);
    }

    public function test_escala_pulada_entra_como_media_das_respondidas_e_nao_como_zero(): void
    {
        // p7 (proposito) pulada; as respondidas tem media 8
        $escalas = ['p4' => 8, 'p5' => 8, 'p6' => 8, 'p7' => null, 'p8' => 8, 'p9' => 8];

        $resultado = $this->service->calcular($escalas, 'prosperidade', null);

        $this->assertSame(80, $resultado['scores_dimensoes']['proposito']);
        $this->assertSame(80, $resultado['score_global']);
        // Sem a correcao, proposito seria 0 e dispararia "desequilibrio"
        $this->assertNotContains('desequilibrio', $resultado['padroes']);
    }

    public function test_crenca_pulada_nao_dispara_bloqueio_crenca(): void
    {
        $escalas = ['p4' => 7, 'p5' => 7, 'p6' => 7, 'p7' => 7, 'p8' => 7, 'p9' => null];

        $resultado = $this->service->calcular($escalas, 'prosperidade', null);

        $this->assertNotContains('bloqueio_crenca', $resultado['padroes']);
        $this->assertSame(70, $resultado['scores_dimensoes']['mentalidade']);
    }

    public function test_ponto_atencao_e_menor_score_com_desempate_pelo_objetivo(): void
    {
        // prosperidade e relacionamentos empatados como menores
        $escalas = ['p4' => 3, 'p5' => 3, 'p6' => 8, 'p7' => 8, 'p8' => 8, 'p9' => 8];

        $comObjetivoNoEmpate = $this->service->calcular($escalas, 'relacionamentos', null);
        $this->assertSame('relacionamentos', $comObjetivoNoEmpate['ponto_atencao']);

        $comSecundarioNoEmpate = $this->service->calcular($escalas, 'proposito', 'relacionamentos');
        $this->assertSame('relacionamentos', $comSecundarioNoEmpate['ponto_atencao']);

        // objetivo fora do empate: vence a ordem da tabela (prosperidade vem primeiro)
        $foraDoEmpate = $this->service->calcular($escalas, 'proposito', null);
        $this->assertSame('prosperidade', $foraDoEmpate['ponto_atencao']);
    }
}
