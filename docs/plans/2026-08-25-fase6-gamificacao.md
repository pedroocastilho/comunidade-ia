# Círculo Aura — Fase 6: Gamificação — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Spec: PRD §11 (versão enxuta, estética da marca).

**Goal:** Streak real de dias seguidos, XP com níveis e conquistas (badges) — percepção de progresso sem infantilizar e sem contaminar o Aura Score.

**Architecture:** `GamificacaoService` central concede XP e conquistas nos pontos existentes (JornadaService/controllers). `users.xp` + tabelas `conquistas` (seed fixa) e `conquista_user`. Streak calculado dos check-ins (sem estado duplicado). UI: streak no card semanal da home, XP/nível + conquistas no Perfil.

## Regras
- XP: ritual +10, aula +15, ação +10, check-in +5, dia completo +20, jornada completa +200, remedição +50.
- Nível: `nivel = floor(sqrt(xp / 100)) + 1` (1→2 com 100xp, 2→3 com 400xp, 3→4 com 900xp...).
- Streak: dias-calendário consecutivos com check-in, terminando hoje ou ontem.
- Conquistas (seed): `primeiro-passo` (1º check-in), `chama-acesa` (streak 7), `constancia-de-ferro` (streak 30), `circulo-completo` (1ª jornada concluída), `renascimento` (1ª remedição), `explorador` (10 conteúdos concluídos).
- O Aura Score NUNCA é afetado por XP/streak.

### Task 1 (TDD): migrations + GamificacaoService (xp, nivel, streak, conquistas) + ganchos nos services/controllers.
### Task 2: UI — streak na home, seção "Sua evolução" no Perfil (nível, barra de XP, conquistas), i18n PT/ES.
### Task 3: admin (resource Conquistas read-only; XP na tabela de usuários), suite completa, browser check, commit.
