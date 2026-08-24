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
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Conteudo de demonstracao (temas de IA) para testar app e painel.
     * Todo o texto e original.
     */
    public function run(): void
    {
        $categorias = collect([
            ['nome' => 'Fundamentos', 'ordem' => 1],
            ['nome' => 'Prompts', 'ordem' => 2],
            ['nome' => 'Automacao', 'ordem' => 3],
            ['nome' => 'Imagem e Video', 'ordem' => 4],
        ])->map(fn ($c) => Categoria::create([
            'nome' => $c['nome'],
            'slug' => Str::slug($c['nome']),
            'ordem' => $c['ordem'],
        ]));

        $instrutor = Instrutor::create([
            'nome' => 'Equipe Comunidade IA',
            'bio' => 'Time de especialistas em inteligencia artificial aplicada.',
        ]);

        $cursos = [
            ['Primeiros Passos com IA', 'Fundamentos', true],
            ['Engenharia de Prompts na Pratica', 'Prompts', false],
            ['Automatizando Tarefas com IA', 'Automacao', false],
            ['Criando Imagens com IA', 'Imagem e Video', false],
        ];

        foreach ($cursos as $i => [$titulo, $catNome, $destaque]) {
            $curso = Curso::create([
                'categoria_id' => $categorias->firstWhere('nome', $catNome)->id,
                'instrutor_id' => $instrutor->id,
                'titulo' => $titulo,
                'slug' => Str::slug($titulo),
                'descricao' => 'Curso pratico e direto ao ponto sobre '.$titulo.'.',
                'status' => 'publicado',
                'destaque' => $destaque,
                'ordem' => $i,
                'views' => ($i + 1) * 100,
            ]);

            foreach (['Introducao', 'Na pratica'] as $m => $tituloModulo) {
                $modulo = Modulo::create([
                    'curso_id' => $curso->id,
                    'titulo' => $tituloModulo,
                    'ordem' => $m,
                ]);

                foreach (range(1, 3) as $a) {
                    Aula::create([
                        'modulo_id' => $modulo->id,
                        'titulo' => "Aula {$a} - {$tituloModulo}",
                        'descricao' => 'Conteudo da aula.',
                        'duracao' => 600,
                        'ordem' => $a,
                    ]);
                }
            }
        }

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            Aviso::create([
                'titulo' => 'Bem-vindo a Comunidade IA',
                'corpo' => 'Novos cursos toda semana. Aproveite!',
                'autor_id' => $admin->id,
                'publicado_em' => now(),
            ]);
        }
    }
}
