# Círculo Aura — Fase 4: Agente Aura — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Spec: PRD §5 e §14.

**Goal:** Chat com a Aura (Claude Haiku 4.5) com memória por usuário, contexto do score/jornada, ferramentas com guardrails, protocolo de crise e classificação de conversas.

**Architecture:** `AnthropicService` (HTTP via Laravel Http, sem SDK) fala com a API `/v1/messages`, com config vinda de `ia_configuracoes`. `AuraChatService` monta o contexto (system prompt + perfil + score + jornada + check-ins + memórias), roda o loop de ferramentas (máx. 3 rodadas) e persiste mensagens. Detector de crise por lista de termos roda ANTES do LLM e responde de forma fixa e segura. Jobs síncronos (queue `sync`) extraem memórias e classificam a conversa após cada troca. UI `App/Aura.vue` com lista de conversas + mensagens; exibição progressiva (typewriter) — streaming SSE real fica anotado para V1.1 (plano de corte §15 permite).

**Tech Stack:** Laravel Http client (mock com `Http::fake` nos testes), modelo `claude-haiku-4-5-20251001`, chave em `ANTHROPIC_API_KEY` (env — **o time precisa adicionar no `.env`**; nunca commitada).

## Global Constraints

- Regras de segurança/crise ficam em CÓDIGO (não no prompt editável do admin).
- A Aura nunca altera score; `sugerir_adaptacao` passa pelo validador de guardrails (PRD §6) antes de aplicar.
- Rate limit: 30 mensagens/10 min por usuário + teto diário de tokens (`ia_configuracoes.teto_diario_tokens`).
- Ferramentas só retornam conteúdo real do catálogo (IDs vindos de busca no banco).

---

### Task 1: AnthropicService (TDD com Http::fake)

**Files:** `app/Services/AnthropicService.php`, `config/services.php` (chave `anthropic`), `tests/Unit/AnthropicServiceTest.php`

**Interfaces:**
```php
AnthropicService::mensagens(array $params): array
// $params: ['system' => string, 'messages' => [...], 'tools' => [...](opcional), 'max_tokens' => int, 'temperature' => float]
// retorna o JSON decodificado da API; lanca AnthropicException em erro HTTP.
```
Modelo/temperatura/max_tokens default vêm de `ia_configuracoes`; endpoint `https://api.anthropic.com/v1/messages`, headers `x-api-key`, `anthropic-version: 2023-06-01`.

- [ ] Testes: monta payload correto (modelo/config do banco), retorna resposta decodificada, lança exceção em 4xx/5xx, envia tools quando fornecidas.
- [ ] Implementa. Commit: `Fase 4: AnthropicService`.

### Task 2: AuraChatService — contexto, ferramentas, crise (TDD)

**Files:** `app/Services/AuraChatService.php`, `app/Services/AuraGuardrails.php`, `tests/Feature/AuraChatServiceTest.php`

**Interfaces:**
```php
AuraChatService::enviar(User $user, AuraConversa $conversa, string $texto): AuraMensagem  // persiste user msg + resposta
AuraChatService::montarContexto(User $user): string                                       // bloco de contexto do system prompt
AuraGuardrails::validarAdaptacao(User $user, int $dia, string $tipo, int $substitutoId): bool
AuraChatService::detectarCrise(string $texto): bool
```
Ferramentas Anthropic: `buscar_conteudo(termo, tipo?)`, `sugerir_adaptacao(dia, atividade, substituto_id, motivo)`, `registrar_checkin(humor, nota?)`.
Crise: lista de termos (suicídio, autolesão, "me matar", "sem saída"...) → resposta fixa acolhedora com CVV 188 + `classificacao = crise`, sem chamada ao LLM.

- [ ] Testes: contexto contém apelido/score/dia da jornada/memórias; crise responde CVV sem chamar API (Http::fake sem chamadas); loop de ferramenta executa `buscar_conteudo` e devolve resultado real; `sugerir_adaptacao` válida aplica no `jornada_dias` com log `adaptado_por_ia` e inválida (dia passado, tipo diferente, id fora do pool de equivalentes) é rejeitada sem mudar nada; mensagens persistidas com tokens.
- [ ] Implementa. Commit: `Fase 4: AuraChatService com ferramentas e protocolo de crise`.

### Task 3: Jobs de memória e classificação (TDD)

**Files:** `app/Jobs/ExtrairMemorias.php`, `app/Services/ClassificadorConversa.php`, `tests/Feature/AuraMemoriaJobTest.php`

- Extração: após resposta da Aura, job pede ao modelo fatos duráveis novos (JSON) e grava `aura_memorias` com `origem = conversa`, deduplicando por conteúdo.
- Classificação: determinística por palavras-chave (prosperidade/relacionamentos/saude/proposito/mentalidade/duvida-plataforma); crise já vem do detector.

- [ ] Testes: job grava memórias sem duplicar; classificador acerta temas por palavra-chave.
- [ ] Implementa. Commit: `Fase 4: memoria e classificacao de conversas`.

### Task 4: Rotas, controller e limites (TDD)

**Files:** `app/Http/Controllers/Web/AuraChatController.php`, `routes/web.php`, `tests/Feature/AuraChatWebTest.php`

**Interfaces:** `GET /aura` (name `aura`) → `App/Aura` com conversas + mensagens da ativa; `POST /aura/mensagem` `{texto, conversa_id?}` → JSON `{mensagem}` (cria conversa se preciso; evento `aura_chat_started` 1x/dia; rate limit `throttle:30,10`; teto diário de tokens retorna 429 amigável).

- [ ] Testes: página renderiza; mensagem cria conversa e retorna resposta (Http::fake); evento 1x/dia; rate limit aplica; teto de tokens bloqueia.
- [ ] Implementa. Commit: `Fase 4: rotas e controller do chat`.

### Task 5: UI do chat + habilitar nav

**Files:** `resources/js/Pages/App/Aura.vue`, `resources/js/Layouts/PainelLayout.vue` (habilita item Aura), `resources/js/Pages/App/Home.vue` (CTA vira link), `resources/js/i18n.js`

- [ ] Chat preto/dourado: histórico, bolhas (Aura com ✦ dourado), input fixo, indicador "Aura está escrevendo...", exibição progressiva da resposta, lista de conversas.
- [ ] `npm run build` + suite verdes. Commit: `Fase 4: interface do chat da Aura`.

### Task 6: Verificação

- [ ] Suite completa; browser: enviar mensagem real se `ANTHROPIC_API_KEY` local existir, senão validar UI com fake. Atualiza roadmap. Commit: `Fase 4: concluida`.
