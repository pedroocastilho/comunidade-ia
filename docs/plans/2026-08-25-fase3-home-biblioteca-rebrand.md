# Círculo Aura — Fase 3: Home diária + Biblioteca + Rebrand — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Spec: PRD §7, §8 e identidade da §1.

**Goal:** Home diária "Bom dia, {apelido} — Dia N" com Ritual/Aula/Ação + check-in, biblioteca com áudios de frequência, e rebrand completo preto/dourado no lugar da paleta esmeralda.

**Architecture:** `JornadaWebController` novo serve a home diária e as ações do dia (concluir atividade, check-in) reutilizando `JornadaService`. `AudioWebController` novo lista e toca áudios com progresso. `PainelController::home` passa a delegar para a home diária (catálogo permanece em `/cursos`, que vira "Biblioteca"). Rebrand em `tailwind.config.js` (tokens `aura.*`), `PainelLayout` e páginas.

**Tech Stack:** Laravel + Inertia + Vue 3 + Tailwind; fontes Google (Cormorant Garamond display + Inter).

## Global Constraints

- Rotas existentes continuam funcionando (`/cursos`, `/aulas/{id}`...); só adicionamos.
- Identidade: preto `#0A0A0A`/`#111`/`#1A1A1A`, dourado `#C9A24B`/`#E5C878`, texto `#F5F0E8`/`#9C948A`.
- Menu: Início · Jornada · Biblioteca · Aura (placeholder Fase 4) · Perfil.
- Eventos: `daily_plan_opened` (1x/dia), `lesson_started`/`lesson_completed` (aula e áudio), `daily_checkin_completed`, `journey_day_completed`.

---

### Task 1: Backend da home diária (TDD)

**Files:**
- Create: `app/Http/Controllers/Web/JornadaWebController.php`
- Modify: `routes/web.php` (adições), `app/Http/Controllers/Web/PainelController.php` (`concluirAula` ganha ponte com jornada; `home` delega)
- Test: `tests/Feature/JornadaWebTest.php`

**Interfaces:**
- Produces: `GET /inicio` → Inertia `App/Home` com props `{jornada: {dia, total_dias, etapa, status}, atividades: {ritual: {…audio}, aula: {…}, acao: {texto, concluida}}, checkin_hoje, progresso_semana: [bool×7], apelido, mensagem}`; `POST /jornada/atividade` `{tipo: ritual|acao}`; `POST /checkin` `{humor: 1-5, texto?}`; `GET /jornada` → `App/Jornada` com os 30 dias e status; sem jornada ativa → home mostra estado "sem jornada" com CTA para a biblioteca.
- `daily_plan_opened` registrado 1x por dia-calendário; `journey_day_completed` quando `avancarSeCompleto` retorna true; `daily_checkin_completed` no POST checkin.

**Steps:**
- [ ] Testes: home renderiza atividades do dia; concluir ritual/ação marca e avança quando completo; checkin registra evento e avança; `daily_plan_opened` não duplica no mesmo dia; home sem jornada não quebra; `/jornada` lista os dias; concluir aula do dia pela rota existente também marca `aula_concluida`.
- [ ] Implementa. Suite verde. Commit: `Fase 3: backend da home diaria e jornada`.

### Task 2: Backend de áudios (TDD)

**Files:**
- Create: `app/Http/Controllers/Web/AudioWebController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/AudioWebTest.php`

**Interfaces:**
- Produces: `GET /audios` → `App/Audios` (publicados, filtro por tipo, busca); `GET /audios/{audio}` → `App/AudioPlayer` (url do arquivo/embed, progresso salvo); `POST /audios/{audio}/progresso` `{posicao_segundos, concluido?}` (upsert `progresso_audios`; concluir ritual do dia se for o áudio do ritual; eventos lesson_started/completed com `tipo: audio`).

**Steps:**
- [ ] Testes: lista só publicados; player renderiza; progresso upserta; concluir áudio do ritual marca `ritual_concluido` na jornada.
- [ ] Implementa. Suite verde. Commit: `Fase 3: backend de audios com progresso`.

### Task 3: Rebrand base (tokens + layout + auth)

**Files:**
- Modify: `tailwind.config.js` (cores `aura.*`, fontes), `resources/css/app.css` (import de fontes, fundo), `resources/js/Layouts/PainelLayout.vue` (dark/gold + menu novo), `resources/js/Layouts/GuestLayout.vue`, páginas de auth (`Auth/Login.vue`, `Auth/Register.vue`), `resources/js/i18n.js` (nav nova PT/ES).

**Steps:**
- [ ] Tokens: `aura-black #0A0A0A`, `aura-surface #141414`, `aura-line #2A2A2A`, `aura-gold #C9A24B`, `aura-gold-light #E5C878`, `aura-text #F5F0E8`, `aura-muted #9C948A`; `font-display` → Cormorant Garamond.
- [ ] PainelLayout: sidebar preta com dourado, logo "Círculo Aura", itens Início/Jornada/Biblioteca/Aura/Perfil (Aura aponta para `#` com badge "em breve" até a Fase 4).
- [ ] Auth pages escuras. `npm run build` verde. Commit: `Fase 3: rebrand preto e dourado no layout e auth`.

### Task 4: Páginas Home diária, Jornada, Biblioteca e Áudios

**Files:**
- Rewrite: `resources/js/Pages/App/Home.vue` (wireframe do PRD §7)
- Create: `resources/js/Pages/App/Jornada.vue`, `resources/js/Pages/App/Audios.vue`, `resources/js/Pages/App/AudioPlayer.vue`
- Modify: `resources/js/Pages/App/Cursos.vue` (dark + abas Cursos/Áudios), `Curso.vue`, `Player.vue` (dark), `resources/js/i18n.js`

**Steps:**
- [ ] Home: saudação por hora do dia, Dia N + etapa, 3 cards de atividade com estado, barra de progresso do dia, CTA "Falar com Aura" (desabilitado até Fase 4), progresso semanal (7 pontos), check-in com emojis.
- [ ] Jornada: mapa dos 30 dias por etapa com status. Áudios: grid com tipo/duração; player com `<audio>` + salvar posição a cada 15s e no pause/fim.
- [ ] `npm run build` + suite verdes. Commit: `Fase 3: home diaria, jornada e biblioteca de audios`.

### Task 5: Verificação final

- [ ] Suite completa + fluxo no browser (login → home diária → concluir atividades → check-in → dia avança; biblioteca e player de áudio). Screenshots.
- [ ] Atualiza roadmap. Commit: `Fase 3: concluida`.
