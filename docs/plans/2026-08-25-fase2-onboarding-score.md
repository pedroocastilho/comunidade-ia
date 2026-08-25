# Círculo Aura — Fase 2: Onboarding web + Aura Score — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Spec: PRD §3, §4 (`docs/specs/2026-08-25-circulo-aura-prd-v1.md`).

**Goal:** Usuário novo responde o questionário de 12 perguntas no primeiro login, recebe Aura Score + Mapa de Manifestação e sai com a jornada criada.

**Architecture:** Middleware web redireciona usuário sem onboarding para o questionário. `OnboardingController` orquestra os services da Fase 1 (`AuraScoreService`, `JornadaService`, `AnalyticsService`). Duas páginas Inertia novas (`App/Onboarding.vue`, `App/AuraScore.vue`) já na identidade preto/dourado (o rebrand do resto vem na Fase 3).

**Tech Stack:** Laravel + Inertia + Vue 3 + Tailwind; i18n PT/ES existente (`resources/js/i18n.js`).

## Global Constraints

- Não alterar rotas/controllers existentes — só adicionar (CLAUDE.md).
- Onboarding é obrigatório e não pulável; uma pergunta por tela; barra de progresso.
- Textos das telas via `useI18n` (PT + ES).
- Testes: `export PATH="/c/php:$PATH" && php artisan test --compact`.

---

### Task 1: Middleware OnboardingCompleto + rotas + controller (TDD)

**Files:**
- Create: `app/Http/Middleware/OnboardingCompleto.php`, `app/Http/Controllers/Web/OnboardingController.php`
- Modify: `routes/web.php` (adições), `bootstrap/app.php` (alias `onboarding.completo`)
- Test: `tests/Feature/OnboardingTest.php`

**Interfaces:**
- Produces: rotas `GET /onboarding` (name `onboarding`), `POST /onboarding` (name `onboarding.salvar`), `GET /aura-score` (name `aura-score`); middleware `onboarding.completo` aplicado ao grupo do painel (home/cursos/aulas) que redireciona para `onboarding` quando `users.onboarding_completo_em` é null.
- Payload POST: `{respostas: {"<pergunta_id>": valor}}`; escalas 0–10 inteiras; obrigatórias validadas contra o banco.

**Steps:**
- [ ] Testes: usuário sem onboarding é redirecionado de `/inicio` para `/onboarding`; usuário completo em `/onboarding` vai para `/inicio`; GET renderiza as 12 perguntas; POST sem obrigatórias falha; POST válido persiste respostas + campos do user (apelido/objetivos/tempo) + memórias (P1, P2, P10, P12) + score calculado + jornada materializada + eventos (`onboarding_started` no GET 1x, `onboarding_completed`, `journey_created`) e redireciona para `/aura-score`; POST repetido não duplica; `/aura-score` renderiza score.
- [ ] Implementa middleware, controller, rotas, alias. Testes verdes. Commit: `Fase 2: onboarding backend com score e jornada`.

### Task 2: Página Onboarding.vue (uma pergunta por tela)

**Files:**
- Create: `resources/js/Pages/App/Onboarding.vue`
- Modify: `resources/js/i18n.js` (chaves `onboarding.*` PT/ES)

**Steps:**
- [ ] Tela preto/dourado, barra de progresso, animação de transição entre perguntas; tipos: `texto` (input), `escala` (botões 0–10), `escolha_unica` (cards); obrigatórias travam o avançar; última tela envia `router.post`.
- [ ] `npm run build` verde. Commit: `Fase 2: tela de onboarding`.

### Task 3: Página AuraScore.vue (revelação + mapa)

**Files:**
- Create: `resources/js/Pages/App/AuraScore.vue`
- Modify: `resources/js/i18n.js` (chaves `score.*` com nomes de dimensões e textos dos 5 padrões PT/ES)

**Steps:**
- [ ] Número global com animação de contagem, 5 barras por dimensão (dourado; destaque no objetivo, marcador no ponto de atenção), cards de padrões, CTA "Começar minha jornada" → `route('home')`.
- [ ] `npm run build` verde. Commit: `Fase 2: tela do Aura Score e mapa`.

### Task 4: Verificação final

- [ ] Suite completa verde; fluxo manual no browser (registro → questionário → score → home).
- [ ] Atualiza roadmap. Commit: `Fase 2: onboarding e score concluidos`.
