<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\Modulo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModeloRelacionamentosTest extends TestCase
{
    use RefreshDatabase;

    public function test_curso_pertence_a_categoria_e_instrutor_e_tem_modulos_com_aulas(): void
    {
        $curso = Curso::factory()
            ->for(Categoria::factory())
            ->for(Instrutor::factory())
            ->create();

        $modulo = Modulo::factory()->for($curso)->create();
        $aula = Aula::factory()->for($modulo)->create();

        $this->assertInstanceOf(Categoria::class, $curso->categoria);
        $this->assertInstanceOf(Instrutor::class, $curso->instrutor);
        $this->assertSame($modulo->id, $curso->modulos->first()->id);
        $this->assertSame($aula->id, $modulo->aulas->first()->id);
        $this->assertSame($modulo->id, $aula->modulo->id);
    }
}
