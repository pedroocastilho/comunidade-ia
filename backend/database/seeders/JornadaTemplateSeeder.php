<?php

namespace Database\Seeders;

use App\Models\Dimensao;
use App\Models\JornadaTemplate;
use App\Models\JornadaTemplateDia;
use Illuminate\Database\Seeder;

/**
 * As 5 jornadas oficiais de 30 dias (PRD secao 6) — uma por dimensao.
 * Cria apenas o que nao existe (firstOrCreate): edicoes do time no admin
 * nunca sao sobrescritas. Ritual (audio) e aula ficam nulos ate o time
 * anexar o conteudo no admin; a acao do dia ja vem escrita.
 */
class JornadaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->jornadas() as $slug => $jornada) {
            $template = JornadaTemplate::firstOrCreate(
                ['titulo' => $jornada['titulo']],
                [
                    'dimensao_id' => Dimensao::where('slug', $slug)->value('id'),
                    'descricao' => $jornada['descricao'],
                    'duracao_dias' => 30,
                    'status' => 'publicado',
                ],
            );

            foreach ($jornada['acoes'] as $indice => $acao) {
                $dia = $indice + 1;
                JornadaTemplateDia::firstOrCreate(
                    ['template_id' => $template->id, 'dia' => $dia],
                    ['etapa' => $this->etapaDoDia($jornada['etapas'], $dia), 'acao_texto' => $acao],
                );
            }
        }
    }

    /** Dias 1-8, 9-16, 17-24, 25-30 -> etapas 1-4. */
    private function etapaDoDia(array $etapas, int $dia): string
    {
        return $etapas[min(3, intdiv($dia - 1, 8))];
    }

    private function jornadas(): array
    {
        return [
            'prosperidade' => [
                'titulo' => 'Jornada da Prosperidade',
                'descricao' => 'Trinta dias para transformar sua relação com dinheiro: consciência, crença, ação e expansão.',
                'etapas' => ['Consciência', 'Crença', 'Ação', 'Expansão'],
                'acoes' => [
                    'Anote 3 coisas pelas quais você é grato(a) hoje — inclusive as pequenas.',
                    'Escreva em uma frase: o que prosperidade significa para você?',
                    'Anote tudo o que você gastar hoje, sem julgamento. Só observe.',
                    'Liste 3 crenças sobre dinheiro que você ouviu na infância.',
                    'Olhe seu extrato dos últimos 7 dias e marque o gasto que mais te surpreendeu.',
                    'Anote uma coisa que o dinheiro já te permitiu viver e que te fez bem.',
                    'Escreva o valor exato que você gostaria de receber por mês. Sem medo do número.',
                    'Releia suas anotações da semana e circule o padrão que mais se repete.',
                    'Escolha a crença mais limitante da sua lista e escreva a versão oposta dela.',
                    'Diga em voz alta hoje: "eu mereço ser bem pago(a) pelo que entrego".',
                    'Anote 3 habilidades suas pelas quais alguém pagaria.',
                    'Lembre de uma conquista financeira sua, por menor que seja, e escreva como ela aconteceu.',
                    'Hoje, agradeça mentalmente cada vez que pagar por algo — você pôde pagar.',
                    'Escreva uma frase de permissão: "está tudo certo em eu querer mais".',
                    'Pergunte a alguém próspero que você admira qual foi a melhor decisão financeira da vida dele.',
                    'Releia a crença nova que você escreveu no dia 9. Ela já soa mais verdadeira?',
                    'Separe hoje qualquer valor — mesmo R$ 5 — para uma reserva. O hábito vale mais que o número.',
                    'Cancele uma assinatura ou gasto recorrente que não faz mais sentido.',
                    'Pesquise quanto o mercado paga por uma das habilidades que você listou.',
                    'Dê o primeiro passo concreto para uma renda extra: uma mensagem, uma pesquisa, um cadastro.',
                    'Negocie algo hoje: um desconto, um prazo, uma tarifa. Treine pedir.',
                    'Defina um teto para um gasto por impulso desta semana — e cumpra.',
                    'Liste o que você entregaria a mais no seu trabalho se fosse dono(a) do negócio.',
                    'Revise a semana: qual ação te deu mais sensação de controle? Repita-a amanhã.',
                    'Escreva sua meta financeira de 90 dias em uma frase clara e realista.',
                    'Divida a meta em 3 marcos mensais. Anote o primeiro no seu calendário.',
                    'Conte sua meta para uma pessoa de confiança. Meta falada cria compromisso.',
                    'Identifique a pessoa ou o conteúdo que mais pode te acelerar — e dê um passo até ela/ele hoje.',
                    'Escreva uma carta curta para você mesmo(a) daqui a 1 ano, vivendo a prosperidade que declarou.',
                    'Releia o dia 1 e o dia 29. Escreva em 3 linhas o que mudou em você.',
                ],
            ],
            'relacionamentos' => [
                'titulo' => 'Jornada dos Relacionamentos',
                'descricao' => 'Trinta dias para curar, se conectar e cultivar as relações que sustentam a sua vida.',
                'etapas' => ['Presença', 'Cura', 'Conexão', 'Cultivo'],
                'acoes' => [
                    'Liste as 5 pessoas mais importantes da sua vida hoje.',
                    'Em uma conversa hoje, guarde o celular e ouça de verdade, sem preparar resposta.',
                    'Anote: com quem você gostaria de estar mais presente?',
                    'Envie uma mensagem sincera de "pensei em você" para alguém querido.',
                    'Observe hoje: em qual relação você mais se sente você mesmo(a)?',
                    'Anote uma qualidade de cada uma das 5 pessoas da sua lista.',
                    'Faça uma refeição com alguém sem nenhuma tela por perto.',
                    'Escreva: o que você mais recebe — e o que mais oferece — nas suas relações?',
                    'Anote o nome de uma pessoa com quem existe um assunto mal resolvido.',
                    'Escreva (sem enviar) tudo o que você diria a essa pessoa. Só desabafe no papel.',
                    'Identifique a sua parte na história: o que você faria diferente?',
                    'Perdoe em silêncio alguém hoje — o perdão é para te liberar, não para absolver o outro.',
                    'Peça desculpas por algo pequeno que ficou pendente com alguém.',
                    'Escreva uma frase de encerramento para uma mágoa antiga: "isso não me define mais".',
                    'Converse com alguém de confiança sobre algo que você nunca contou.',
                    'Releia o dia 9. O peso diminuiu? Anote o que mudou.',
                    'Faça hoje uma pergunta genuína a alguém: "como você está de verdade?" — e escute.',
                    'Elogie uma pessoa de forma específica: diga o quê e por quê.',
                    'Convide alguém para um encontro simples esta semana: um café, uma caminhada.',
                    'Reconecte-se com alguém distante: uma mensagem resgatando uma boa memória.',
                    'Diga "não" com gentileza a algo que te afastaria de quem importa.',
                    'Agradeça a alguém por algo que essa pessoa nem sabe que te marcou.',
                    'Pergunte a alguém próximo: "o que eu poderia fazer mais por você?"',
                    'Revise a semana: qual conexão te surpreendeu? Anote.',
                    'Crie um pequeno ritual com alguém: um café semanal, uma ligação de domingo.',
                    'Planeje um gesto concreto para uma das 5 pessoas da sua lista — e marque a data.',
                    'Defina um limite saudável em uma relação que te drena. Escreva como vai comunicá-lo.',
                    'Escreva o tipo de presença que você quer ser na vida de quem ama.',
                    'Agradeça por escrito (mensagem ou carta) à pessoa que mais te sustentou até aqui.',
                    'Releia o dia 1. Escreva em 3 linhas como suas relações mudaram nestes 30 dias.',
                ],
            ],
            'saude-energia' => [
                'titulo' => 'Jornada da Saúde e Energia',
                'descricao' => 'Trinta dias para recuperar o ritmo do corpo: percepção, sono, vitalidade e renovação.',
                'etapas' => ['Percepção', 'Ritmo', 'Vitalidade', 'Renovação'],
                'acoes' => [
                    'Anote de 0 a 10 sua energia ao acordar, no meio do dia e à noite.',
                    'Beba um copo de água em jejum e observe como o corpo responde.',
                    'Anote a que horas você dormiu e acordou. Só observe, sem meta ainda.',
                    'Perceba hoje: em qual momento do dia sua energia despenca? O que aconteceu antes?',
                    'Faça 10 respirações profundas em algum momento de tensão e anote o efeito.',
                    'Liste o que te dá energia e o que te rouba energia. Duas colunas.',
                    'Caminhe 10 minutos ao ar livre, sem fone, só percebendo o corpo.',
                    'Releia a semana: qual é o maior ladrão de energia da sua rotina?',
                    'Defina um horário-alvo para dormir hoje — e prepare o quarto 30 minutos antes.',
                    'Troque uma bebida do dia por água.',
                    'Nada de telas 30 minutos antes de dormir hoje.',
                    'Faça 5 minutos de alongamento ao acordar.',
                    'Coma hoje uma refeição inteira sem pressa e sem tela, mastigando de verdade.',
                    'Exponha-se ao sol da manhã por 10 minutos.',
                    'Repita o horário-alvo de sono. Constância vale mais que perfeição.',
                    'Compare sua energia de hoje com o dia 1. Anote a diferença.',
                    'Mova o corpo por 20 minutos hoje, do jeito que for: caminhada, dança, treino.',
                    'Adicione uma fruta ou verdura a mais no seu dia.',
                    'Suba escadas em vez de elevador hoje, quando puder.',
                    'Convide alguém para se mover com você esta semana.',
                    'Teste uma pausa de 5 minutos a cada 90 de trabalho. Anote o efeito no fim do dia.',
                    'Prepare (ou escolha) hoje uma refeição pensando em nutrição, não só em pressa.',
                    'Faça um mini-treino de força: 10 agachamentos, 10 flexões (do seu jeito), 30s de prancha.',
                    'Revise a semana: qual hábito novo foi mais fácil? Ele é seu candidato a permanente.',
                    'Agende o check-up ou exame que você está adiando.',
                    'Monte sua manhã ideal em 3 passos simples — e viva-a amanhã.',
                    'Escolha o único hábito desta jornada que você levará para sempre. Escreva o porquê.',
                    'Presenteie o corpo: um banho longo, uma massagem, um descanso sem culpa.',
                    'Escreva uma nota de gratidão ao seu corpo por tudo que ele sustenta.',
                    'Releia o dia 1. Anote em 3 linhas o que seu corpo te ensinou em 30 dias.',
                ],
            ],
            'proposito' => [
                'titulo' => 'Jornada do Propósito',
                'descricao' => 'Trinta dias para escutar a própria vida: clareza, direção e o primeiro movimento.',
                'etapas' => ['Escuta', 'Clareza', 'Direção', 'Movimento'],
                'acoes' => [
                    'Anote 3 momentos da sua vida em que você se sentiu plenamente vivo(a).',
                    'Escreva: o que você faria se soubesse que não poderia falhar?',
                    'Liste 5 assuntos que você estuda ou consome por puro prazer.',
                    'Pergunte a 2 pessoas próximas: "qual é o meu maior talento aos seus olhos?"',
                    'Anote o que te indigna no mundo — indignação aponta para propósito.',
                    'Lembre da sua infância: do que você brincava por horas sem cansar?',
                    'Escreva sem parar por 5 minutos começando com "eu sinto que nasci para...".',
                    'Releia a semana e circule as palavras que mais se repetem.',
                    'Escreva em UMA frase o que você quer construir com a sua vida. Rascunho vale.',
                    'Liste o que precisa ser verdade daqui a 5 anos para você sentir orgulho.',
                    'Identifique o que você faz hoje que JÁ está alinhado com essa frase.',
                    'Identifique o que você faz hoje que te afasta dela.',
                    'Refine sua frase de propósito: deixe-a mais curta e mais sua.',
                    'Encontre uma pessoa que vive algo parecido com o que você quer. Estude a trajetória dela.',
                    'Diga sua frase de propósito em voz alta para você mesmo(a) no espelho.',
                    'Conte sua frase para uma pessoa de confiança e anote a reação.',
                    'Defina um projeto de 90 dias que expresse seu propósito em escala pequena.',
                    'Quebre o projeto em 3 entregas. Anote a primeira com prazo.',
                    'Reserve na agenda um bloco fixo semanal para esse projeto. Trate como sagrado.',
                    'Elimine um compromisso que existe só por obrigação e não te leva a lugar algum.',
                    'Dê hoje um passo público, por menor que seja: um post, uma conversa, uma inscrição.',
                    'Liste os recursos que você já tem (pessoas, habilidades, ferramentas) para o projeto.',
                    'Peça ajuda a uma pessoa específica para uma parte específica do projeto.',
                    'Revise a semana: o que travou? Reduza a próxima entrega até caber na sua rotina.',
                    'Trabalhe 25 minutos focados no seu projeto. Só isso. Sem perfeição.',
                    'Documente seu progresso: uma foto, um parágrafo, um registro do que já existe.',
                    'Compartilhe com alguém o que você está construindo e por quê.',
                    'Escreva como será sua vida quando esse projeto der frutos. Detalhe um dia inteiro.',
                    'Defina o próximo marco pós-jornada e a data em que vai revisá-lo.',
                    'Releia o dia 1. Escreva em 3 linhas o que ficou claro sobre o seu caminho.',
                ],
            ],
            'mentalidade' => [
                'titulo' => 'Jornada da Mentalidade',
                'descricao' => 'Trinta dias para aquietar a mente: silêncio, observação, reprogramação e domínio.',
                'etapas' => ['Silêncio', 'Observação', 'Reprogramação', 'Domínio'],
                'acoes' => [
                    'Fique 5 minutos em silêncio total hoje, sem celular. Só respire.',
                    'Anote os 3 pensamentos que mais se repetiram na sua cabeça hoje.',
                    'Ao acordar, não pegue o celular nos primeiros 15 minutos.',
                    'Observe uma preocupação de hoje e pergunte: "isso está sob meu controle?"',
                    'Faça uma atividade cotidiana (café, banho, caminhada) com atenção total.',
                    'Anote: qual é o pensamento que mais te rouba paz?',
                    'Passe 1 hora de hoje sem redes sociais e anote o que sentiu.',
                    'Releia a semana: sua mente é mais amiga ou mais crítica? Escreva sem julgamento.',
                    'Escreva a crítica que você mais se faz — e como diria a mesma coisa a um amigo.',
                    'Cada vez que se criticar hoje, complete com: "...e estou aprendendo".',
                    'Anote 3 evidências reais de que você é capaz (fatos, não opiniões).',
                    'Escolha uma frase-âncora para momentos de ansiedade e use-a hoje.',
                    'Transforme uma reclamação de hoje em um pedido ou uma ação.',
                    'Escreva a história que você conta sobre si — e reescreva o final.',
                    'Passe o dia sem reclamar em voz alta. Se escapar, recomece. Anote como foi.',
                    'Releia o dia 9. A voz interna mudou de tom? Anote.',
                    'Medite (ou apenas respire fundo) por 10 minutos hoje.',
                    'Diante de uma irritação hoje, espere 90 segundos antes de reagir.',
                    'Anote à noite: qual foi seu melhor pensamento do dia?',
                    'Faça algo levemente desconfortável hoje de propósito — e observe a mente antes e depois.',
                    'Organize um espaço físico pequeno (mesa, gaveta). Ambiente externo acalma o interno.',
                    'Escreva suas preocupações num papel, feche-o e marque 15 minutos para pensar só nelas.',
                    'Pratique dizer "eu escolho" no lugar de "eu tenho que" durante o dia todo.',
                    'Revise a semana: em qual situação você respondeu em vez de reagir? Celebre.',
                    'Ensine a alguém a técnica desta jornada que mais te ajudou.',
                    'Escreva seu "protocolo de dias difíceis": 3 passos para quando a mente pesar.',
                    'Releia suas anotações do dia 2 e do dia 19. Compare os pensamentos dominantes.',
                    'Defina o ritual mental que você levará para sempre (silêncio da manhã, âncora, pausa).',
                    'Escreva uma carta de paz para a sua mente: o que vocês construíram juntas em 30 dias.',
                    'Releia o dia 1. Anote em 3 linhas o que mudou no seu diálogo interno.',
                ],
            ],
        ];
    }
}
