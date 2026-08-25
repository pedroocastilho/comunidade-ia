# Círculo Aura — Fase 8: Comunidade "O Círculo" — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Spec: PRD §10 (versão enxuta).

**Goal:** Feed entre membros: posts, reações ✦, comentários, denúncias; moderação completa no admin.

**Architecture:** Tabelas `posts` (status publicado|oculto, fixado), `post_reacoes` (unique user+post), `post_comentarios` (status), `denuncias` (polimórfica: post|comentario). Página `/circulo` no menu (ícone users): compor + feed (fixados primeiro) + reagir + comentar + denunciar. Rate limit de postagem. Admin: ocultar posts/comentários, resolver denúncias, fixar post.

### Task 1 (TDD): migrations + models + rotas/controller web (feed, criar post, reagir/desreagir, comentar, denunciar; validações e limites).
### Task 2: UI Circulo.vue (estética da marca; avatar com iniciais, tempo relativo, ✦ contador), nav + i18n PT/ES.
### Task 3: admin (resources Posts/Denúncias com ações de moderação), suite, browser check, commit.
