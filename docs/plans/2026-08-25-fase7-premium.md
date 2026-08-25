# Círculo Aura — Fase 7: Conteúdo Premium — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Spec: PRD §9.

**Goal:** Cursos e áudios avulsos fora da assinatura: 🔒 na vitrine, página de venda com checkout externo, webhook `compra_aprovada` liberando na hora.

**Architecture:** Tabela `compras` (user, produto_externo_id, curso/audio, payload). Campo `checkout_url` em cursos/audios. Gate `podeAcessar` nos controllers de curso/aula/áudio: premium sem compra → tela de venda (evento `premium_viewed`). Webhook existente ganha evento `compra_aprovada` (email + produto_externo_id) → cria compra + `premium_purchased`.

### Task 1 (TDD): migration compras + checkout_url; ProcessadorWebhookPagamento com compra_aprovada; User::comprou(produtoExternoId).
### Task 2 (TDD): gates web (curso/aula/áudio premium bloqueiam sem compra; liberam com compra ou premium=false), tela de venda (banner 🔒 + CTA checkout), badges 🔒 nos cards, premium_viewed.
### Task 3: admin (campos premium/checkout_url/produto_externo_id nos forms de curso e áudio; resource Compras read-only), suite, browser check, commit.
