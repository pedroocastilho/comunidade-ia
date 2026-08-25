# Círculo Aura V1 — Roadmap de implementação

> Spec: `docs/specs/2026-08-25-circulo-aura-prd-v1.md`
> Cada fase produz software funcional e testável sozinha, com plano próprio em `docs/plans/`.

| Fase | Plano | Entrega | Status |
|------|-------|---------|--------|
| 1 | `2026-08-25-fase1-fundacao-dados-score.md` | Migrations, models, seeds, fórmula do Aura Score, motor de jornada (services + testes). Backend puro. | ✅ concluída (2026-08-25) |
| 2 | `2026-08-25-fase2-onboarding-score.md` | Questionário (12 perguntas), tela Score + Mapa de Manifestação, criação da jornada, redirect de onboarding. | ✅ concluída (2026-08-25, verificada no browser) |
| 3 | `2026-08-25-fase3-home-biblioteca-rebrand.md` | Home diária (ritual/aula/ação/check-in), biblioteca com áudios de frequência, player de áudio, rebrand preto/dourado. | ✅ concluída (2026-08-25, verificada no browser) |
| 4 | `2026-08-25-fase4-agente-aura.md` | Chat com Claude Haiku 4.5, memória, ferramentas com guardrails, protocolo de crise, extração/classificação. Exibição progressiva (streaming SSE real: V1.1). Requer `ANTHROPIC_API_KEY` no .env. | ✅ concluída (2026-08-25) |
| 5 | `2026-08-25-fase5-admin-webhooks-analytics.md` | Resources Filament novos, webhook genérico de pagamento, dashboard de métricas, rate limits. | ✅ concluída (2026-08-25, verificada no browser) |

| 6 | `2026-08-25-fase6-gamificacao.md` | Streak de check-ins, XP com níveis, conquistas (badges) no perfil. | — |
| 7 | `2026-08-25-fase7-premium.md` | Conteúdo premium avulso: lock 🔒, página de venda, webhook compra_aprovada, liberação automática. | — |
| 8 | `2026-08-25-fase8-comunidade.md` | Feed "O Círculo": posts, reações ✦, comentários, denúncias e moderação. | — |

Regras herdadas do PRD: plano de corte na §15 do spec; app Flutter congelado; commits PT-BR sem acentos.
