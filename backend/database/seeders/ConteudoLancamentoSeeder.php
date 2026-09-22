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
 * Remove o conteudo demo de IA e cria os 5 modulos prontos: Comece Aqui,
 * Codigo da Manifestacao, Mente e Corpo, Relacionamento (PDFs) e Prosperidade.
 *
 * Idempotente: pode rodar mais de uma vez sem duplicar nada.
 * Capas ficam em public/capas; videos em public/videos ou no servidor da
 * Diamond; PDFs em storage/app/materiais (servidos pela rota autenticada
 * "material", por isso o material_url nao tem extensao .pdf).
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
                    ['titulo' => 'Boas-vindas e Apresentação', 'video_url' => 'https://circuloaura.com/videos/comece-aqui-1.mp4'],
                ],
            ],
            [
                'categoria' => 'Código da Manifestação',
                'slug' => 'codigo-da-manifestacao',
                'ordem' => 2,
                'descricao' => 'A jornada pela história, pela ciência e pelas tradições que buscaram o poder da mente.',
                'destaque' => false,
                'views' => 280,
                'aulas' => [
                    ['titulo' => 'A pergunta que toda civilização já fez', 'video_url' => 'https://circuloaura.com/videos/codigo-da-manifestacao-1.mp4'],
                    ['titulo' => 'Por que orar, meditar e visualizar nunca saíram de moda', 'video_url' => 'https://circuloaura.com/videos/codigo-da-manifestacao-2.mp4'],
                    ['titulo' => 'O que os antigos sabiam sobre a mente', 'video_url' => 'https://circuloaura.com/videos/codigo-da-manifestacao-3.mp4'],
                    ['titulo' => 'Rituais que atravessaram 5 mil anos e o que eles têm em comum', 'video_url' => 'https://circuloaura.com/videos/codigo-da-manifestacao-4.mp4'],
                    ['titulo' => 'A oração e a visualização são a mesma coisa?', 'video_url' => 'https://circuloaura.com/videos/codigo-da-manifestacao-5.mp4'],
                ],
            ],
            [
                'categoria' => 'Mente e Corpo',
                'slug' => 'mente-e-corpo',
                'ordem' => 3,
                'descricao' => 'Práticas guiadas para equilibrar a mente, descansar e expandir a consciência.',
                'destaque' => false,
                'views' => 250,
                'aulas' => [
                    ['titulo' => 'Equilíbrio Interior', 'video_url' => 'https://circuloaura.com/videos/mente-e-corpo-1.mp4'],
                    ['titulo' => 'Descanso Mental', 'descricao' => 'Para a mente que não para de pensar.', 'video_url' => 'https://circuloaura.com/videos/mente-e-corpo-2.mp4'],
                    ['titulo' => 'Expansão da consciência', 'video_url' => 'https://circuloaura.com/videos/mente-e-corpo-3.mp4'],
                    ['titulo' => 'Sons de cura emocional e mental', 'video_url' => 'https://circuloaura.com/videos/mente-e-corpo-4.mp4'],
                    ['titulo' => 'Técnica tai chi chuan para relaxar e meditar', 'video_url' => 'https://circuloaura.com/videos/mente-e-corpo-5.mp4'],
                ],
            ],
            [
                'categoria' => 'Relacionamento',
                'slug' => 'relacionamento',
                'ordem' => 4,
                'descricao' => 'Como você ama, por que ama assim e o amor que você está construindo.',
                'destaque' => false,
                'views' => 150,
                'aulas' => [
                    ['titulo' => 'O casal que se apaixonou respondendo 36 perguntas', 'material_url' => '/materiais/relacionamento-aula-1'],
                    ['titulo' => 'O jeito como você ama pode ter nascido antes de você aprender a falar', 'material_url' => '/materiais/relacionamento-aula-2'],
                    ['titulo' => 'Nem todo mundo sente amor da mesma forma', 'material_url' => '/materiais/relacionamento-aula-3'],
                    ['titulo' => 'Você só aceita o amor que acredita merecer', 'material_url' => '/materiais/relacionamento-aula-4'],
                    ['titulo' => 'O amor que você está construindo já começou', 'material_url' => '/materiais/relacionamento-aula-5'],
                ],
            ],
            [
                'categoria' => 'Prosperidade',
                'slug' => 'prosperidade',
                'ordem' => 5,
                'descricao' => 'Práticas de gratidão, merecimento e abertura para receber prosperidade.',
                'destaque' => false,
                'views' => 200,
                'aulas' => [
                    ['titulo' => 'A verdade sobre tudo que existe', 'video_url' => 'https://circuloaura.com/videos/prosperidade-1.mp4'],
                    ['titulo' => '4 formas de praticar e atrair o que você deseja', 'video_url' => 'https://circuloaura.com/videos/prosperidade-2.mp4'],
                    ['titulo' => 'Canto da Gratidão — um hino para conexão celestial e merecimento', 'video_url' => 'https://circuloaura.com/videos/prosperidade-3.mp4'],
                    ['titulo' => "Ho'oponopono para receber dinheiro e destravar a prosperidade", 'video_url' => 'https://circuloaura.com/videos/prosperidade-4.mp4'],
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
                        'video_url' => $aula['video_url'] ?? null,
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
