<?php

namespace Database\Seeders;

use App\Models\Dimensao;
use App\Models\QuestionarioPergunta;
use Illuminate\Database\Seeder;

class QuestionarioSeeder extends Seeder
{
    /**
     * As 12 perguntas do onboarding (PRD secao 3).
     * A pontuacao e fixa em codigo (AuraScoreService); aqui so texto/tipo/opcoes.
     */
    public function run(): void
    {
        $objetivos = [
            ['valor' => 'prosperidade', 'rotulo' => 'Prosperidade'],
            ['valor' => 'relacionamentos', 'rotulo' => 'Relacionamentos'],
            ['valor' => 'saude-energia', 'rotulo' => 'Saúde e Energia'],
            ['valor' => 'proposito', 'rotulo' => 'Propósito e Carreira'],
            ['valor' => 'mentalidade', 'rotulo' => 'Mentalidade e Paz interior'],
        ];

        $dimensaoId = fn (string $slug) => Dimensao::where('slug', $slug)->value('id');

        $perguntas = [
            ['ordem' => 1, 'tipo' => 'texto', 'texto' => 'Como você quer ser chamado(a)?', 'obrigatoria' => true, 'alimenta_memoria' => true],
            ['ordem' => 2, 'tipo' => 'escolha_unica', 'texto' => 'O que você mais quer transformar agora?', 'opcoes' => $objetivos, 'obrigatoria' => true, 'alimenta_memoria' => true],
            ['ordem' => 3, 'tipo' => 'escolha_unica', 'texto' => 'E em segundo lugar?', 'opcoes' => $objetivos, 'obrigatoria' => false],
            ['ordem' => 4, 'tipo' => 'escala', 'texto' => 'De 0 a 10, como está sua vida financeira hoje?', 'obrigatoria' => true, 'dimensao_id' => $dimensaoId('prosperidade')],
            ['ordem' => 5, 'tipo' => 'escala', 'texto' => 'De 0 a 10, como estão seus relacionamentos (amor, família, amizades)?', 'obrigatoria' => true, 'dimensao_id' => $dimensaoId('relacionamentos')],
            ['ordem' => 6, 'tipo' => 'escala', 'texto' => 'De 0 a 10, como estão sua saúde e sua energia no dia a dia?', 'obrigatoria' => true, 'dimensao_id' => $dimensaoId('saude-energia')],
            ['ordem' => 7, 'tipo' => 'escala', 'texto' => 'De 0 a 10, o quanto você sente que sua vida tem direção e propósito?', 'obrigatoria' => true, 'dimensao_id' => $dimensaoId('proposito')],
            ['ordem' => 8, 'tipo' => 'escala', 'texto' => 'De 0 a 10, como está sua mente (paz, foco, ansiedade)?', 'obrigatoria' => true, 'dimensao_id' => $dimensaoId('mentalidade')],
            ['ordem' => 9, 'tipo' => 'escala', 'texto' => 'De 0 a 10, o quanto você acredita que sua realidade pode mudar nos próximos 90 dias?', 'obrigatoria' => true, 'dimensao_id' => $dimensaoId('mentalidade')],
            ['ordem' => 10, 'tipo' => 'escolha_unica', 'texto' => 'O que mais te trava hoje?', 'opcoes' => [
                ['valor' => 'tempo', 'rotulo' => 'Falta de tempo'],
                ['valor' => 'disciplina', 'rotulo' => 'Falta de disciplina'],
                ['valor' => 'direcao', 'rotulo' => 'Não sei por onde começar'],
                ['valor' => 'medo', 'rotulo' => 'Medo e insegurança'],
                ['valor' => 'ambiente', 'rotulo' => 'Pessoas e ambiente ao redor'],
            ], 'obrigatoria' => true, 'alimenta_memoria' => true],
            ['ordem' => 11, 'tipo' => 'escolha_unica', 'texto' => 'Quanto tempo por dia você tem para sua transformação?', 'opcoes' => [
                ['valor' => '5-10', 'rotulo' => '5 a 10 minutos'],
                ['valor' => '15-20', 'rotulo' => '15 a 20 minutos'],
                ['valor' => '30+', 'rotulo' => '30 minutos ou mais'],
            ], 'obrigatoria' => true],
            ['ordem' => 12, 'tipo' => 'texto', 'texto' => 'Descreva em poucas frases a vida que você quer manifestar.', 'obrigatoria' => false, 'alimenta_memoria' => true],
        ];

        foreach ($perguntas as $pergunta) {
            QuestionarioPergunta::updateOrCreate(['ordem' => $pergunta['ordem']], $pergunta);
        }
    }
}
