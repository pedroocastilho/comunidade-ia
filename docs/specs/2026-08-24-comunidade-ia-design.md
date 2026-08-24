# Comunidade IA — Documento de Especificação (App 1)

> **Status:** desenho aprovado (2026-08-24), aguardando revisão do spec.
> **Nome do produto:** provisório "Comunidade IA" — nome definitivo a definir (ver §11).
> **Autoria:** Diamond Global / Pedro Castilho.

---

## 1. Visão geral

Área de membros (web + app nativo) para venda de uma **comunidade de cursos sobre Inteligência Artificial**, no formato de assinatura única de **R$ 997**. O produto segue a experiência já validada do MeuFluxo (catálogo de cursos em vídeo, com progresso e "continuar assistindo"), porém com escopo mais enxuto e um diferencial: uma **camada de comunidade leve** (comentários por aula + mural de avisos).

"IA" aqui é apenas o **tema** dos cursos — o app não executa nenhuma funcionalidade de IA internamente.

### Relação com o segundo produto ("Dream Life")
Este é o **App 1**. Depois dele, um segundo produto ("Dream Life") será um clone fiel do MeuFluxo para concorrer diretamente (e-books, áudios/frequências, workshops, feed social). O App 2 é um projeto separado, fora do escopo deste documento. Tudo aqui é modelado para que a base sirva de fundação reaproveitável no App 2.

---

## 2. Objetivos e não-objetivos

### Objetivos (MVP)
- Aluno se cadastra, faz login e assiste aos cursos de IA em vídeo (web e app).
- Acompanhar progresso e retomar de onde parou.
- Explorar/buscar cursos por tema; ver "em alta"; montar "minha lista".
- **Baixar aulas para assistir offline** (app).
- Comentar/tirar dúvidas em cada aula; ver mural de avisos da Diamond.
- Painel administrativo para a Diamond cadastrar conteúdo e liberar acessos.

### Não-objetivos (ficam para fase 2 / App 2)
- Feed social aberto entre membros.
- Playlists do usuário, workshops ao vivo, áudios/frequências, e-books.
- Pagamento dentro das lojas (in-app purchase).
- Qualquer funcionalidade de IA rodando dentro do app.

---

## 3. Stack

| Camada | Tecnologia | Observação |
|---|---|---|
| API/Backend | Laravel 12 + Sanctum | Uma API única serve app e web (mesma stack da plataformaIA) |
| App nativo | Flutter | Um código-base → iOS + Android |
| Web | Vue 3 + Inertia + Tailwind | Layout desktop com sidebar |
| Vídeo | Bunny Stream | Streaming protegido por token + CDN; suporte a download offline |
| Admin | Filament (Laravel) | CRUD de conteúdo, moderação, avisos, liberação de acesso |
| Banco | MySQL/MariaDB | |

**Marca:** paleta e tipografia **próprias** (ver §11) — não reutilizar as cores/fonte exatas do MeuFluxo.

---

## 4. Modelo de dados

Tabelas principais (nomes de colunas em PT-BR, padrão do projeto).

### users
`id`, `name`, `email` (unique), `phone`, `password`, `role` (`admin` | `aluno`, default `aluno`), `tem_acesso` (bool, default false), `acesso_expira_em` (date, nullable), `email_verified_at`, `timestamps`.

### instrutores
`id`, `nome`, `bio` (text), `foto_url`, `timestamps`.

### categorias
`id`, `nome`, `slug`, `icone` (url/nome), `ordem`, `timestamps`.

### cursos
`id`, `categoria_id` (fk), `instrutor_id` (fk, nullable), `titulo`, `slug`, `descricao` (text), `capa_url`, `banner_url`, `duracao_total` (segundos, calculado), `ordem`, `status` (`rascunho` | `publicado`), `destaque` (bool), `views` (int, default 0), `timestamps`.

### modulos
`id`, `curso_id` (fk), `titulo`, `ordem`, `timestamps`.

### aulas
`id`, `modulo_id` (fk), `titulo`, `descricao` (text), `bunny_library_id`, `bunny_video_id`, `duracao` (segundos), `material_url` (nullable, PDF/anexo), `ordem`, `is_bonus` (bool, default false), `liberada_em` (datetime, nullable — para drip futuro), `views` (int, default 0), `timestamps`.

### progresso_aulas
`id`, `user_id` (fk), `aula_id` (fk), `concluida` (bool, default false), `posicao_segundos` (int, default 0), `updated_at`. **Unique** (`user_id`, `aula_id`).

### minha_lista
`id`, `user_id` (fk), `curso_id` (fk), `created_at`. **Unique** (`user_id`, `curso_id`).

### comentarios
`id`, `user_id` (fk), `aula_id` (fk), `texto` (text), `comentario_pai_id` (fk self, nullable — respostas), `aprovado` (bool, default true), `timestamps`.

### avisos
`id`, `titulo`, `corpo` (text), `imagem_url` (nullable), `autor_id` (fk users, admin), `publicado_em` (datetime), `timestamps`.

### Relacionamentos
- categoria 1—N cursos; instrutor 1—N cursos.
- curso 1—N modulos 1—N aulas.
- user N—N aulas (via progresso_aulas); user N—N cursos (via minha_lista).
- aula 1—N comentarios (auto-relacionamento para respostas).

---

## 5. Controle de acesso

- **Autenticação:** Sanctum (token para o app; sessão/cookie para a web Inertia).
- **Autorização de conteúdo:** middleware `acesso.ativo` verifica `users.tem_acesso == true` e (se houver) `acesso_expira_em >= hoje`. Endpoints de conteúdo e download exigem esse middleware.
- **MVP:** acesso liberado manualmente pela Diamond no admin (Filament) ao confirmar a venda.
- **Evolução:** endpoint `POST /webhooks/venda` (fase seguinte) recebe a confirmação do gateway (Kiwify/Hubla/Guru/etc.) e liga `tem_acesso` automaticamente. Método de pagamento definido depois; **sem** compra dentro das lojas no MVP (evita a taxa de 15–30% da Apple/Google).
- **Papéis:** `admin` acessa o Filament; `aluno` acessa app/web.

---

## 6. API (REST, prefixo `/api/v1`)

### Autenticação
- `POST /auth/register` — nome, email, telefone, senha.
- `POST /auth/login` — retorna token.
- `POST /auth/logout`.
- `POST /auth/forgot-password` / `POST /auth/reset-password`.

### Perfil
- `GET /me` — dados + status de acesso.
- `PUT /me` — atualizar dados.
- `PUT /me/password` — senha atual + nova.
- `DELETE /me` — exclusão de conta e dados (LGPD).

### Conteúdo (exige `acesso.ativo`)
- `GET /home` — destaque, continuar assistindo, trilhas de cursos, últimos avisos.
- `GET /categorias`.
- `GET /cursos?categoria=&busca=&em_alta=` — listagem/filtros.
- `GET /cursos/{slug}` — curso com módulos, aulas, instrutor e progresso do usuário.
- `GET /aulas/{id}` — dados da aula + URL de streaming assinada (token Bunny) + posição salva.
- `POST /aulas/{id}/concluir` — marca concluída.
- `PUT /aulas/{id}/progresso` — salva `posicao_segundos`.
- `GET /em-alta` — cursos/aulas mais vistos.
- `GET /minha-lista` / `POST /minha-lista/{cursoId}` / `DELETE /minha-lista/{cursoId}`.
- `GET /aulas/{id}/download` — URL MP4 assinada para download offline (Bunny), gated por acesso.

### Comunidade (exige `acesso.ativo`)
- `GET /aulas/{id}/comentarios`.
- `POST /aulas/{id}/comentarios` — texto (e `comentario_pai_id` opcional).
- `DELETE /comentarios/{id}` — apenas do próprio autor (admin remove qualquer via Filament).
- `GET /avisos` — mural.

---

## 7. Telas — App (Flutter)

**Navegação:** bottom nav com 4 abas + avatar/perfil no topo.

1. **Entrada:** Splash → Onboarding (3 slides) → Login / Cadastro (2 passos: dados → senha) → Esqueci minha senha.
2. **Início:** banner de destaque, "Continue de onde parou", trilhas por categoria, mural de avisos.
3. **Explorar:** busca + filtro por tema/categoria (grade de cursos).
4. **Em alta:** ranking de cursos/aulas mais vistos.
5. **Minha Lista:** favoritos + **Downloads offline** (aulas baixadas).
6. **Detalhe do curso:** capa/banner, descrição, instrutor, progresso, lista de módulos/aulas, botão iniciar/continuar, adicionar à minha lista.
7. **Player:** vídeo (Bunny), controles, marcar concluída, próxima aula, material anexo, botão baixar, aba de **comentários/dúvidas** da aula.
8. **Perfil/Config:** dados pessoais, alterar senha, minha assinatura (status/validade), avisos, Fale Conosco (WhatsApp/FAQ), termos/política (in-app browser), avaliar app, sair, **excluir conta (LGPD)**.

---

## 8. Telas — Web (Vue + Inertia)

Mesmas funcionalidades, layout desktop com **sidebar** (Início, Cursos, Configurações, Sair) e barra superior (categorias, busca, avatar):
- Login / Cadastro / Recuperar senha.
- Início (destaque + continuar + trilhas + avisos).
- Catálogo / Busca / Categoria.
- Detalhe do curso.
- Player (vídeo + comentários).
- Configurações (dados, senha, assinatura).

Downloads offline é exclusivo do app (a web usa streaming).

---

## 9. Admin (Filament)

CRUD e operações para a equipe Diamond:
- Instrutores, Categorias, Cursos, Módulos, Aulas (com `bunny_video_id`).
- Avisos (publicar no mural).
- Comentários (moderar/remover).
- Usuários: **liberar/revogar acesso** (`tem_acesso`, `acesso_expira_em`), ver progresso.
- Relatórios simples: views por curso/aula, alunos ativos.

---

## 10. Integração de vídeo (Bunny Stream)

- Cada aula referencia `bunny_library_id` + `bunny_video_id`.
- Streaming servido via **URL assinada com token** (chave da biblioteca no `.env`, nunca no cliente).
- Download offline: endpoint gera URL MP4 assinada e temporária; o Flutter armazena o arquivo cifrado localmente e valida acesso ao abrir.
- Upload: no MVP, a Diamond sobe o vídeo no painel da Bunny e cola o `video_id` no Filament (upload direto via API fica para depois).

---

## 11. Marca e identidade (a definir)

- **Nome definitivo:** pendente. "Comunidade IA" é provisório. Evitar qualquer nome/identidade próximo de "MeuFluxo" (risco de marca).
- **Paleta:** própria, distinta do roxo do MeuFluxo. Sugestão inicial a validar no design.
- **Tipografia:** fonte própria ou Google Font (ex.: Poppins/Sora) — não reutilizar a fonte proprietária deles.
- Todo texto, copy e arte devem ser **originais** (não copiar descrições, depoimentos ou materiais do MeuFluxo).

---

## 12. Fases

- **Fase 1 (este MVP):** tudo em §2 Objetivos.
- **Fase 1.1:** webhook de venda automatizando `tem_acesso`.
- **Fase 2 / App 2 ("Dream Life"):** clone fiel do MeuFluxo — feed social, playlists, workshops, áudios, e-books.

---

## 13. Riscos e decisões em aberto

- **Nome/identidade** ainda não definidos (§11).
- **Gateway de pagamento** a decidir (Kiwify/Hubla/Guru/Stripe) — não bloqueia o MVP (acesso manual no início).
- **Custo Bunny** proporcional a armazenamento/streaming — dimensionar conforme catálogo.
- Download offline exige cuidado com proteção do conteúdo (arquivo cifrado + expiração de acesso).
