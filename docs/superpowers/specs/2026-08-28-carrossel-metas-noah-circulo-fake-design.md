# Design: carrossel Em alta, calendario de metas, Noah e movimento fake no Circulo

Data: 2026-08-28. Aprovado pelo Pedro na conversa.

Regras herdadas do CLAUDE.md: nao renomear tabelas, rotas, models ou controllers existentes;
migrations sempre novas; commits so com pedido explicito.

## 1. "Em alta" em carrossel continuo

- `resources/js/Pages/App/Home.vue`, secao "Em alta" (prop `em_alta`).
- Trocar `overflow-x-auto` por faixa marquee: lista duplicada no DOM, `@keyframes`
  translateX 0 -> -50%, ~40s por volta, `animation-play-state: paused` no hover.
- `prefers-reduced-motion`: sem animacao, volta ao scroll horizontal normal.
- Demais linhas horizontais (Continuar, Audios destaque) nao mudam.

## 2. Calendario de metas

- Migration nova `metas`: `id`, `user_id` (fk users, cascade), `titulo` string(120),
  `prazo` date, `concluida_em` datetime nullable, timestamps. Index (user_id, prazo).
- Model `Meta` (`app/Models/Meta.php`), relacao `user()`, scopes `emAndamento`,
  `concluidas`, `vencidas` (prazo < hoje e nao concluida).
- Controller `Web\MetaController`: `index` (Inertia `App/Metas`), `store`
  (valida titulo 1..120, prazo date >= hoje), `update` (alterna concluida_em),
  `destroy`. Autorizacao: so o dono (abort 403).
- Rotas dentro do grupo `auth, acesso.web, onboarding.completo`:
  `GET /metas` (metas), `POST /metas` (metas.store), `PATCH /metas/{meta}` (metas.update),
  `DELETE /metas/{meta}` (metas.destroy).
- Pagina `App/Metas.vue`: calendario mensal com navegacao de mes, ponto dourado nos dias
  com prazo; clicar no dia abre form inline com aquele prazo. Lista em tres grupos:
  Em andamento / Concluidas / Vencidas, com botoes "Fiz" e "Nao fiz" (desfaz) e excluir.
- Home: card "Suas metas" com ate 3 metas em andamento mais proximas do prazo + link.
  Prop `metas_proximas` adicionada no `JornadaWebController::home`.
- Menu (`PainelLayout.vue`): item "Metas" (i18n `nav.metas`, icone `target`).
- Sem XP/gamificacao nesta versao.

## 3. Noah no lugar da Aura (agente)

- Somente o agente muda de nome. Marca "Circulo Aura" e "Aura Score" continuam.
- Interface: chaves i18n PT/ES do chat (`aura.*`, `nav.aura`), titulo, empty state,
  aviso, placeholder -> "Noah". Tabelas, rotas (`/aura`), classes PHP intocadas.
- Prompt: migration nova que faz `update ia_configuracoes set valor = ...` na chave
  `system_prompt` trocando a persona para Noah (masculino). Seeder
  `IaConfiguracaoSeeder` atualizado para novas instalacoes. Regras fixas em
  `AuraChatService::REGRAS_FIXAS` ajustadas se citarem o nome.
- Chat `App/Aura.vue`:
  - Avatar redondo `/noah.jpg` (arquivo em `public/`, entregue depois pelo Pedro) no
    header e ao lado de cada balao do assistente; fallback "N" dourado se a imagem
    falhar (`@error`).
  - Estados: ao enviar -> balao "Noah esta pensando..." com tres pontos pulsando;
    ao receber -> "Noah esta escrevendo..." e typewriter com velocidade natural
    (variacao por caractere, pausa curta em pontuacao); avatar com pulso dourado
    enquanto escreve. Entrada dos baloes com fade + slide.
  - Backend do chat nao muda (continua nao-streaming).

## 4. Movimento fake no Circulo

- Migration nova: coluna `perfil_ficticio` boolean default false em `users`.
- Seeder `PerfisFicticiosSeeder`: ~40 usuarios com nomes brasileiros variados
  (alguns so apelido), email `ficticio+N@circuloaura.local`, senha aleatoria,
  `perfil_ficticio = true`, `tem_acesso = false`, `onboarding_completo_em` preenchido.
  Middleware de login nao precisa mudar: eles nao tem senha conhecida.
- Excluir `perfil_ficticio` das metricas do admin (widgets que contam users) e de
  qualquer contagem de membros exibida.
- Banco de frases `database/data/circulo_frases.php`: arrays `posts` (~60),
  `comentarios` (~100), `respostas` (~40), PT-BR coloquial, positivas, falando de
  progresso, do Noah, dos audios, da jornada, dando forca ao autor. Variacoes aplicadas
  em runtime: emoji opcional, pontuacao final opcional, {nome} do autor quando houver.
- Comando `circulo:movimentar` (`app/Console/Commands/CirculoMovimentar.php`),
  agendado `hourly()` em `routes/console.php` (`bootstrap/app.php` ganha
  `withSchedule`). Logica por execucao:
  - Posts fakes: meta diaria sorteada uma vez por dia (0, 0, 1, 1, 1, 2 com pesos) e
    guardada em cache; a cada hora, chance proporcional para publicar um, com
    `created_at` em minuto aleatorio da hora corrente.
  - Curtidas: para cada post (real ou fake) com menos de 48h, sortear 0-2 curtidas de
    perfis que ainda nao curtiram, ate um teto por post (1-6, sorteado e guardado em
    cache por post).
  - Comentarios: por post com menos de 72h, teto por post sorteado (40% zero, 30% um,
    15% dois, 10% tres, 5% quatro); a cada hora, chance de adicionar um se abaixo do
    teto. Em post real, primeiro comentario so apos 20-90 min da publicacao.
  - Respostas: 30% de chance de um comentario fake ganhar uma resposta curta (mesmo
    modelo `post_comentarios`, sem thread), com `created_at` posterior.
  - Regras: mesmo perfil nao comenta duas vezes o mesmo post; frase nao repete no mesmo
    post; conteudo fake nasce `publicado` ignorando `circulo_moderacao_previa`.
  - Opcao `--simular-horas=N` para rodar N iteracoes retroativas (teste local e
    preencher feed vazio no lancamento).
- Cron no servidor: `* * * * * php artisan schedule:run`. Local: `schedule:work`.

## Ordem de implementacao

1 -> 3 -> 2 -> 4.

## Testes

- Feature tests: MetaController (CRUD e 403), comando `circulo:movimentar` (cria
  registros apenas de perfis ficticios, respeita tetos, nao duplica curtida).
- Verificacao manual no browser: carrossel, chat do Noah, calendario.
