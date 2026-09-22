<?php

namespace Database\Seeders;

use App\Models\Aula;
use App\Models\Aviso;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Conteudo real do lancamento (documento do Gabriel, 2026-09-21).
 * Remove o conteudo demo de IA e cria os 4 modulos prontos:
 * Comece Aqui, Mente e Corpo, Relacionamento (PDFs) e Prosperidade.
 *
 * Idempotente: pode rodar mais de uma vez sem duplicar nada.
 * Os bunny_video_id ficam vazios ate os videos subirem para o Bunny Stream;
 * as capas e PDFs sao enviados para public/capas e public/materiais no deploy.
 */
class ConteudoLancamentoSeeder extends Seeder
{
    public function run(): void
    {
        $this->removerConteudoDemo();

        $modulos = [
            [
                'categoria' => 'Comece Aqui',
                'slug' => 'comece-aqui',
                'ordem' => 1,
                'descricao' => 'Boas-vindas ao Círculo Aura: comece sua jornada por aqui.',
                'destaque' => true,
                'views' => 300,
                'aulas' => [
                    ['titulo' => 'Boas-vindas e Apresentação'],
                ],
            ],
            [
                'categoria' => 'Mente e Corpo',
                'slug' => 'mente-e-corpo',
                'ordem' => 2,
                'descricao' => 'Práticas guiadas para equilibrar a mente, descansar e expandir a consciência.',
                'destaque' => false,
                'views' => 250,
                'aulas' => [
                    ['titulo' => 'Equilíbrio Interior'],
                    ['titulo' => 'Descanso Mental', 'descricao' => 'Para a mente que não para de pensar.'],
                    ['titulo' => 'Expansão da consciência'],
                    ['titulo' => 'Sons de cura emocional e mental'],
                    ['titulo' => 'Técnica tai chi chuan para relaxar e meditar'],
                ],
            ],
            [
                'categoria' => 'Relacionamento',
                'slug' => 'relacionamento',
                'ordem' => 3,
                'descricao' => 'Como você ama, por que ama assim e o amor que você está construindo.',
                'destaque' => false,
                'views' => 150,
                'aulas' => [
                    ['titulo' => 'O casal que se apaixonou respondendo 36 perguntas', 'material_url' => '/materiais/relacionamento-aula-1.pdf'],
                    ['titulo' => 'O jeito como você ama pode ter nascido antes de você aprender a falar', 'material_url' => '/materiais/relacionamento-aula-2.pdf'],
                    ['titulo' => 'Nem todo mundo sente amor da mesma forma', 'material_url' => '/materiais/relacionamento-aula-3.pdf'],
                    ['titulo' => 'Você só aceita o amor que acredita merecer', 'material_url' => '/materiais/relacionamento-aula-4.pdf'],
                    ['titulo' => 'O amor que você está construindo já começou', 'material_url' => '/materiais/relacionamento-aula-5.pdf'],
                ],
            ],
            [
                'categoria' => 'Prosperidade',
                'slug' => 'prosperidade',
                'ordem' => 4,
                'descricao' => 'Práticas de gratidão, merecimento e abertura para receber prosperidade.',
                'destaque' => false,
                'views' => 200,
                'aulas' => [
                    ['titulo' => 'A verdade sobre tudo que existe'],
                    ['titulo' => '4 formas de praticar e atrair o que você deseja'],
                    ['titulo' => 'Canto da Gratidão — um hino para conexão celestial e merecimento'],
                    ['titulo' => "Ho'oponopono para receber dinheiro e destravar a prosperidade"],
                ],
            ],
        ];

        foreach ($modulos as $dados) {
            $categoria = Categoria::updateOrCreate(
                ['slug' => $dados['slug']],
                ['nome' => $dados['categoria'], 'ordem' => $dados['ordem']],
            );

            $curso = Curso::updateOrCreate(
                ['slug' => $dados['slug']],
                [
                    'categoria_id' => $categoria->id,
                    'titulo' => $dados['categoria'],
                    'descricao' => $dados['descricao'],
                    'capa_url' => "/capas/{$dados['slug']}.png",
                    'status' => 'publicado',
                    'destaque' => $dados['destaque'],
                    'ordem' => $dados['ordem'],
                    'views' => $dados['views'],
                ],
            );

            $modulo = Modulo::updateOrCreate(
                ['curso_id' => $curso->id, 'ordem' => 1],
                ['titulo' => $dados['categoria']],
            );

            foreach ($dados['aulas'] as $i => $aula) {
                Aula::updateOrCreate(
                    ['modulo_id' => $modulo->id, 'ordem' => $i + 1],
                    [
                        'titulo' => $aula['titulo'],
                        'descricao' => $aula['descricao'] ?? null,
                        'material_url' => $aula['material_url'] ?? null,
                    ],
                );
            }
        }

        $this->avisoDeBoasVindas();
    }

    /**
     * Apaga o conteudo demo de IA (DemoSeeder) se ainda existir.
     * Categorias em cascata levam cursos, modulos e aulas demo junto.
     */
    private function removerConteudoDemo(): void
    {
        Categoria::whereIn('slug', ['fundamentos', 'prompts', 'automacao', 'imagem-e-video'])->delete();
        Instrutor::where('nome', 'Equipe Comunidade IA')->delete();
        Aviso::where('titulo', 'Bem-vindo a Comunidade IA')->delete();
    }

    private function avisoDeBoasVindas(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            return;
        }

        Aviso::firstOrCreate(
            ['titulo' => 'Bem-vindo ao Círculo Aura'],
            [
                'corpo' => 'Sua jornada de manifestação começa agora. Explore o módulo Comece Aqui.',
                'autor_id' => $admin->id,
                'publicado_em' => now(),
            ],
        );
    }
}
