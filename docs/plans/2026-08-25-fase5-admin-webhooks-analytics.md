# Círculo Aura — Fase 5: Admin + Webhooks + Analytics — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Spec: PRD §12, §13, §16 e contrato de webhook.

**Goal:** Time opera o produto sem dev (conteúdo, jornadas, perguntas, IA, usuários, métricas) e a assinatura recorrente libera/bloqueia acesso automaticamente por webhook.

**Architecture:** Resources Filament v5 gerados por `make:filament-resource --generate` e ajustados. Webhook único `POST /webhooks/pagamento/{plataforma}` com token secreto por env, adapter genérico traduzindo payload → eventos internos; `tem_acesso` é o gate (o middleware existente não muda). Widget de métricas no dashboard do Filament.

## Global Constraints

- Webhook grava TODO payload cru em `webhooks_pagamento` antes de processar (reprocesso no admin).
- Eventos internos: `assinatura_ativa` (cria/ativa user por e-mail), `assinatura_cancelada` (bloqueia), `pagamento_atrasado` (marca status, mantém acesso — carência), `pagamento_regularizado` (reativa).
- Token: header `X-Webhook-Token` comparado com `WEBHOOK_PAGAMENTO_TOKEN` (env). Sem token válido → 401.

### Task 1: Webhook de pagamento (TDD)
Files: `app/Http/Controllers/Api/WebhookPagamentoController.php`, `app/Services/ProcessadorWebhookPagamento.php`, `routes/api.php` (adição), `config/services.php`, `tests/Feature/WebhookPagamentoTest.php`.
- [ ] Testes: 401 sem token; payload cru salvo; assinatura_ativa cria user novo (senha aleatória) com `tem_acesso`/`assinatura_status=ativa` e ativa user existente; cancelada bloqueia; atrasada mantém acesso; regularizada reativa; payload desconhecido salvo com erro e responde 200 (não reentrega infinita).
- [ ] Commit: `Fase 5: webhook generico de pagamento`.

### Task 2: Resources Filament
Files: `app/Filament/Resources/{Audios,JornadaTemplates,JornadaTemplateDias,QuestionarioPerguntas,IaConfiguracoes,WebhooksPagamento,AuraConversas}/...` via `--generate`, ajustes de rótulos/campos; `tests/Feature/AdminAuraTest.php` (páginas respondem 200 para admin).
- [ ] IaConfiguracoes: só listar/editar (sem criar/apagar). WebhooksPagamento e AuraConversas: leitura. Commit: `Fase 5: resources do admin`.

### Task 3: Métricas + eventos restantes
Files: `app/Filament/Widgets/MetricasAura.php`, registro no painel; `PainelController::aula` (evento `lesson_started`), rota `/sem-acesso` (evento `subscription_blocked_view`).
- [ ] Widget: usuários com acesso, onboarding completos, ativos hoje (daily_plan_opened), check-ins hoje, conversas com a Aura hoje, conversas de crise (7d).
- [ ] Commit: `Fase 5: metricas no admin e eventos restantes`.

### Task 4: Verificação final
- [ ] Suite completa; `migrate:fresh --seed`; build; screenshot do admin. Roadmap atualizado. Commit: `Fase 5: concluida`.
