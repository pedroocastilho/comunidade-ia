# Círculo Aura

Plataforma web de manifestação e desenvolvimento pessoal por assinatura recorrente. O membro responde um questionário no primeiro acesso, recebe seu **Aura Score** (fórmula determinística, 0–100 em 5 dimensões), ganha uma **jornada diária de 30 dias** (ritual em áudio + aula em vídeo + ação) e é acompanhado pela **Aura**, agente de IA com memória.

Projeto da Diamond Global. Identidade: dark quente + dourado, referência Dunity.

> Este repositório nasceu como "Comunidade IA" (cursos de IA) e pivotou para o Círculo Aura em 2026-08-25. A base de catálogo de cursos (Bunny Stream, progresso, admin) foi reaproveitada como Biblioteca.

## Stack

- **API/Web:** Laravel 12 + Sanctum + Inertia (Vue 3 + Tailwind)
- **IA:** Claude Haiku 4.5 via API Anthropic (`ANTHROPIC_API_KEY` no `.env`)
- **Vídeo/Áudio:** Bunny Stream
- **Admin:** Filament (conteúdo, jornadas, questionário, prompts da IA, métricas, webhooks)
- **App:** Flutter (**congelado** até a web validar)

## Documentação

- PRD V1: [`docs/specs/2026-08-25-circulo-aura-prd-v1.md`](docs/specs/2026-08-25-circulo-aura-prd-v1.md)
- Roadmap de fases: [`docs/plans/2026-08-25-circulo-aura-roadmap.md`](docs/plans/2026-08-25-circulo-aura-roadmap.md)
- Spec original do Comunidade IA (histórico): [`docs/specs/2026-08-24-comunidade-ia-design.md`](docs/specs/2026-08-24-comunidade-ia-design.md)

## Rodando local

```bash
cd backend
composer install && npm install
cp .env.example .env && php artisan key:generate   # preencher ANTHROPIC_API_KEY etc.
php artisan migrate --seed
npm run build   # ou npm run dev
php artisan serve
```

Testes: `php artisan test --compact` (PHP local em `C:\php`; requer extensão `intl`).

## Status

V1 implementada (Fases 1–5): onboarding + score, jornada diária, biblioteca com áudios, chat da Aura, admin, webhook de assinatura e analytics. Falta para lançar: chave da API no ambiente, conteúdo real (cursos/áudios/templates) e escolha da plataforma de checkout.
