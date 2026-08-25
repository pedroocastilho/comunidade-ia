# Círculo Aura V1 — Roadmap de implementação

> Spec: `docs/specs/2026-08-25-circulo-aura-prd-v1.md`
> Cada fase produz software funcional e testável sozinha, com plano próprio em `docs/plans/`.

| Fase | Plano | Entrega | Status |
|------|-------|---------|--------|
| 1 | `2026-08-25-fase1-fundacao-dados-score.md` | Migrations, models, seeds, fórmula do Aura Score, motor de jornada (services + testes). Backend puro. | ✅ concluída (2026-08-25) |
| 2 | `2026-08-25-fase2-onboarding-score.md` | Questionário (12 perguntas), tela Score + Mapa de Manifestação, criação da jornada, redirect de onboarding. | ✅ concluída (2026-08-25, verificada no browser) |
| 3 | `2026-08-25-fase3-home-biblioteca-rebrand.md` | Home diária (ritual/aula/ação/check-in), biblioteca com áudios de frequência, player de áudio, rebrand preto/dourado. | ✅ concluída (2026-08-25, verificada no browser) |
| 4 | fase4-agente-aura | Chat com Claude Haiku 4.5 (streaming), memória, ferramentas com guardrails, protocolo de crise, jobs de extração/classificação. | — |
| 5 | fase5-admin-webhooks-analytics | Resources Filament novos, webhook genérico de pagamento, dashboard de métricas, rate limits, flag para desligar comentários. | — |

Regras herdadas do PRD: plano de corte na §15 do spec; app Flutter congelado; commits PT-BR sem acentos.
