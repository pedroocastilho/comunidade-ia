# Plano: carrossel Em alta, Noah, calendario de metas, movimento fake no Circulo

> Spec: docs/superpowers/specs/2026-08-28-carrossel-metas-noah-circulo-fake-design.md
> Execucao inline nesta sessao, tarefa por tarefa, com `php artisan test --compact` ao fim de cada uma.
> Sem commits automaticos (regra do CLAUDE.md).

**Goal:** entregar as 4 mudancas aprovadas em 2026-08-28 sem renomear nada existente.

**Stack:** Laravel 12, Inertia, Vue 3, Tailwind 3, SQLite em testes.

## Global Constraints
- Nunca renomear tabelas, rotas, models, controllers existentes. Migrations sempre novas.
- Textos de interface em PT-BR (+ ES no i18n). Codigo/comentarios PT-BR sem acentos nos commits.
- Noah substitui somente o nome do agente; "Circulo Aura" e "Aura Score" ficam.

### Task 1: Carrossel continuo no "Em alta"
- Modify: `backend/resources/js/Pages/App/Home.vue` (secao em_alta) — marquee CSS, lista duplicada, pausa no hover, reduced-motion.
- Verificar: `npm run build` sem erro; abrir /inicio.

### Task 2: Noah — nome, prompt e chat vivo
- Modify: `backend/resources/js/i18n.js` (pt/es `nav.aura`, `aura.*`, `home.falarComAura`, `home.auraEmBreve`).
- Modify: `backend/database/seeders/IaConfiguracaoSeeder.php` (persona Noah, masculino).
- Create: `backend/database/migrations/2026_08_28_100001_atualiza_prompt_noah.php` (update da chave `system_prompt` se ainda contiver "Você é a Aura").
- Modify: `backend/app/Services/AuraChatService.php` REGRAS_FIXAS ("medico" masculino) e `app/Jobs/ExtrairMemorias.php` label.
- Modify: `backend/resources/js/Pages/App/Aura.vue` — avatar `/noah.jpg` com fallback, estados pensando/escrevendo, typewriter natural, animacao de entrada.
- Test: `tests/Feature/AuraSeedTest.php` deve continuar passando; ajustar asserts se citarem "Aura".

### Task 3: Calendario de metas
- Create: migration `2026_08_28_100002_create_metas_table.php`, `app/Models/Meta.php`,
  `app/Http/Controllers/Web/MetaController.php`, `resources/js/Pages/App/Metas.vue`,
  `tests/Feature/MetasTest.php`.
- Modify: `routes/web.php` (4 rotas), `PainelLayout.vue` (item nav), `AppIcon.vue` (icone `target`),
  `i18n.js` (`nav.metas`, `metas.*`), `JornadaWebController::home` (`metas_proximas`), `Home.vue` (card).
- Test: CRUD, 403 de outro usuario, validacao de prazo.

### Task 4: Movimento fake no Circulo
- Create: migration `2026_08_28_100003_add_perfil_ficticio_to_users.php`,
  `database/seeders/PerfisFicticiosSeeder.php`, `database/data/circulo_frases.php`,
  `app/Services/CirculoMovimentoService.php`, `app/Console/Commands/CirculoMovimentar.php`,
  `tests/Feature/CirculoMovimentoTest.php`.
- Modify: `User` fillable/casts, `DatabaseSeeder`, `routes/console.php` (hourly), `bootstrap/app.php` (withSchedule),
  `MetricasAura.php` (excluir ficticios), `UserResource` (coluna/filtro opcional).
- Test: comando cria apenas com perfis ficticios; nao duplica curtida; respeita tetos; `--simular-horas`.
