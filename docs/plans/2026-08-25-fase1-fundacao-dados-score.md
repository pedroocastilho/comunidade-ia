# Círculo Aura — Fase 1: Fundação de dados + Aura Score — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans para implementar tarefa a tarefa. Steps usam checkboxes (`- [ ]`).

**Goal:** Criar toda a camada de dados do Círculo Aura (migrations, models, factories, seeds) e os dois motores determinísticos — fórmula do Aura Score e materialização/avanço de jornada — cobertos por testes.

**Architecture:** Backend Laravel puro, sem UI. Migrations aditivas (nunca editar existentes). Services em `app/Services` com lógica pura testável por unit tests. Seeds fixos para dimensões, perguntas do questionário e configurações de IA.

**Tech Stack:** Laravel 13 (repo), PHP 8.5, PHPUnit 12, SQLite em testes.

## Global Constraints

- Colunas e nomes em PT-BR (padrão do repo); commits em PT-BR **sem acentos**.
- Nunca editar migrations/models/rotas existentes — só adicionar (CLAUDE.md do projeto).
- Models seguem padrão do repo: `protected $guarded = []` OU atributos `#[Fillable]`; casts explícitos.
- Schemas exatos das tabelas: PRD §16 (`docs/specs/2026-08-25-circulo-aura-prd-v1.md`). Fórmulas: PRD §4. Regras de jornada: PRD §6.
- Rodar testes com: `export PATH="/c/php:$PATH" && php artisan test --compact`.
- O LLM nunca participa do cálculo de score (tudo em PHP puro).

---

### Task 1: Migrations e models do domínio Aura

**Files:**
- Create: `database/migrations/2026_08_25_1000xx_*.php` (uma migration por grupo abaixo)
- Create: `app/Models/{Dimensao,QuestionarioPergunta,QuestionarioResposta,AuraScore,Audio,ProgressoAudio,JornadaTemplate,JornadaTemplateDia,Jornada,JornadaDia,Checkin,AuraConversa,AuraMensagem,AuraMemoria,IaConfiguracao,EventoAnalytics,WebhookPagamento}.php`
- Modify: `app/Models/User.php` (adicionar fillable + casts + relacionamentos novos)
- Test: `tests/Feature/AuraModeloTest.php`

**Interfaces:**
- Produces: models Eloquent com os nomes acima; relacionamentos `User→auraScores/jornadas/checkins/auraMemorias/auraConversas`, `Jornada→dias`, `JornadaTemplate→dias`, `AuraConversa→mensagens`.

**Steps:**
- [ ] Migration aditiva em `users`: `apelido`, `objetivo_principal`, `objetivo_secundario`, `tempo_disponivel`, `onboarding_completo_em`, `assinatura_status` (default `manual`) — PRD §16.
- [ ] Migration aditiva em `cursos` e `aulas`: `premium` (bool false), `produto_externo_id` (nullable); `aulas.tags` (json nullable).
- [ ] Migrations novas (schemas exatos no PRD §16): `dimensoes`, `questionario_perguntas`, `questionario_respostas` (unique user+pergunta), `aura_scores`, `audios`, `progresso_audios` (unique user+audio), `jornada_templates`, `jornada_template_dias`, `jornadas`, `jornada_dias`, `checkins`, `aura_conversas`, `aura_mensagens`, `aura_memorias`, `ia_configuracoes`, `eventos_analytics`, `webhooks_pagamento`.
- [ ] Models com casts (json→array, bools, datetimes) e relacionamentos; factories para os usados em teste.
- [ ] Teste `AuraModeloTest`: cria grafo completo via factories e verifica relacionamentos e uniques.
- [ ] `php artisan test --compact` verde. Commit: `Fase 1: migrations e models do dominio Aura`.

### Task 2: Seeds fixos (dimensões, questionário, config IA)

**Files:**
- Create: `database/seeders/{DimensaoSeeder,QuestionarioSeeder,IaConfiguracaoSeeder}.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/AuraSeedTest.php`

**Interfaces:**
- Produces: 5 dimensões com slugs `prosperidade|relacionamentos|saude-energia|proposito|mentalidade`; 12 perguntas do PRD §3 (ordem 1–12, tipos e opções exatos); chaves `ia_configuracoes`: `modelo` (`claude-haiku-4-5-20251001`), `system_prompt`, `temperatura` (`1.0`), `max_tokens` (`1024`), `teto_diario_tokens` (`50000`).

**Steps:**
- [ ] Seeders idempotentes (`updateOrCreate` por slug/ordem/chave).
- [ ] Teste: roda seeders, verifica 5 dimensões, 12 perguntas (P2 com 5 opções, P4–P9 escala, P12 opcional), chaves de IA presentes.
- [ ] Testes verdes. Commit: `Fase 1: seeds de dimensoes, questionario e config IA`.

### Task 3: AuraScoreService (fórmula determinística)

**Files:**
- Create: `app/Services/AuraScoreService.php`
- Test: `tests/Unit/AuraScoreServiceTest.php`

**Interfaces:**
- Consumes: respostas como array `['p4' => int0-10, 'p5' => ..., 'p6' => ..., 'p7' => ..., 'p8' => ..., 'p9' => ...]` + `objetivoPrincipal` (slug) + `objetivoSecundario` (slug|null).
- Produces:
  ```php
  AuraScoreService::calcular(array $escalas, string $objetivoPrincipal, ?string $objetivoSecundario): array
  // retorna: ['score_global' => int, 'scores_dimensoes' => [slug => int], 'dimensao_prioritaria' => slug, 'ponto_atencao' => slug, 'padroes' => [slug...]]
  AuraScoreService::salvar(User $user, array $resultado): AuraScore
  ```

**Steps (TDD):**
- [ ] Testes primeiro, cobrindo PRD §4: score por dimensão = escala×10; mentalidade = round((p8×10 + p9×10)/2); pesos 2.0/1.5/1.0; global ponderado arredondado; ponto de atenção = menor score com desempates (objetivo principal/secundário → ordem da tabela); padrões nas 5 regras com acúmulo máx. 2 e ordem de avaliação (`reconstrucao`, `desequilibrio`, `bloqueio_crenca`, `pronto_para_acelerar`, `base_solida` como fallback exclusivo).
- [ ] Casos de teste mínimos: tudo 10 (global 100, `pronto_para_acelerar` exige p9≥7), tudo 0 (`reconstrucao`+`bloqueio_crenca`), desequilíbrio >30, empate de menor score resolvido pelo objetivo, sem objetivo secundário.
- [ ] Implementa serviço; testes verdes. Commit: `Fase 1: AuraScoreService com formula e padroes`.

### Task 4: JornadaService (materialização e avanço)

**Files:**
- Create: `app/Services/JornadaService.php`
- Test: `tests/Feature/JornadaServiceTest.php`

**Interfaces:**
- Produces:
  ```php
  JornadaService::criarParaUsuario(User $user): ?Jornada          // template da dimensao do objetivo principal; materializa 30 jornada_dias; aplica variante_curta se tempo_disponivel = '5-10'
  JornadaService::diaAtual(Jornada $j): ?JornadaDia
  JornadaService::concluirAtividade(JornadaDia $dia, string $tipo): JornadaDia  // tipo: ritual|aula|acao
  JornadaService::registrarCheckin(User $user, int $humor, ?string $texto): Checkin
  JornadaService::avancarSeCompleto(Jornada $j): bool             // avanca se 3 atividades OU checkin do dia; max 1 avanco por dia-calendario; conclui jornada no dia 30
  ```

**Steps (TDD):**
- [ ] Testes primeiro: materialização copia template (30 dias, campos), variante curta substitui quando existe, avanço pelas duas vias, trava de 1 avanço/dia-calendário (congelar tempo com `Carbon::setTestNow`), dias sem acesso não pulam, conclusão no dia 30, check-in único por dia.
- [ ] Implementa; testes verdes. Commit: `Fase 1: JornadaService com materializacao e avanco`.

### Task 5: Analytics server-side

**Files:**
- Create: `app/Services/AnalyticsService.php`
- Test: `tests/Unit/AnalyticsServiceTest.php`

**Interfaces:**
- Produces: `AnalyticsService::registrar(string $nome, ?User $user = null, array $props = []): void` — insere em `eventos_analytics`; nomes válidos são os do PRD §13 (constante `EVENTOS`); nome inválido lança `InvalidArgumentException` em ambiente local/testing e é ignorado silenciosamente em produção.

**Steps (TDD):**
- [ ] Testes: registra evento com props; rejeita nome fora da lista em testing.
- [ ] Implementa; verde. Commit: `Fase 1: AnalyticsService de eventos`.

### Task 6: Verificação final da fase

- [ ] Suite completa verde (`php artisan test --compact`), incluindo os 62 testes pré-existentes.
- [ ] `php artisan migrate:fresh --seed` funciona do zero.
- [ ] Atualiza status da Fase 1 no roadmap. Commit: `Fase 1: fundacao concluida`.
