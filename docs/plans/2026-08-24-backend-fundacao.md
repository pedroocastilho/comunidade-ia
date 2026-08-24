# Fundação do Backend — Plano de Implementação (Plano 1 de 5)

> **Para workers agênticos:** SUB-SKILL OBRIGATÓRIA: usar superpowers:subagent-driven-development (recomendado) ou superpowers:executing-plans para implementar tarefa a tarefa. Os passos usam checkbox (`- [ ]`) para rastreio.

**Goal:** Subir a base do backend Laravel — projeto configurado, banco modelado, autenticação (cadastro/login/perfil/senha/exclusão) e middleware de controle de acesso — pronta para as APIs de conteúdo do Plano 2.

**Architecture:** Monorepo. O backend Laravel 12 fica em `/backend` e serve tanto a API (para o app Flutter) quanto, mais adiante, a web Inertia e o admin Filament. Autenticação por Sanctum (tokens para o app). Acesso ao conteúdo é gated por uma flag `tem_acesso` no usuário, verificada por um middleware.

**Tech Stack:** Laravel 12, PHP 8.4, Sanctum, MySQL/MariaDB, PHPUnit (testes de feature).

## Global Constraints

- PHP 8.4, Laravel 12.
- Colunas e comentários em PT-BR; mensagens de commit em PT-BR sem acentos.
- Nunca commitar `.env` (só `.env.example`).
- Apps nativos nunca vendem: a API não expõe preço/checkout. Venda é web-only (fora do escopo deste plano).
- Todo texto/copy é original (não copiar conteúdo do MeuFluxo).
- Monorepo: backend em `/backend`, mobile (futuro) em `/mobile`.

---

### Task 1: Scaffold do backend Laravel

**Files:**
- Create: `backend/` (projeto Laravel completo)
- Modify: `.gitignore` (raiz do repo)
- Create: `backend/.env.example`

**Interfaces:**
- Produces: projeto Laravel funcional em `/backend` com Sanctum instalado e `routes/api.php` habilitado; suíte de testes rodando.

- [ ] **Step 1: Criar o projeto Laravel em /backend**

Na raiz do repo (`comunidade-ia/`):

```bash
composer create-project laravel/laravel backend
cd backend
php artisan install:api
```

`install:api` cria `routes/api.php`, publica o Sanctum e adiciona a migration de `personal_access_tokens`.

- [ ] **Step 2: Corrigir o .gitignore da raiz (Flutter vai em /mobile, não /app)**

Substituir o bloco Flutter do `.gitignore` da raiz por caminhos `mobile/` e ignorar dependências do backend:

```gitignore
# Backend (Laravel)
/backend/vendor/
/backend/node_modules/
/backend/public/build/
/backend/public/hot
/backend/storage/*.key
/backend/.env
/backend/.env.*
!/backend/.env.example

# Mobile (Flutter) - futuro
/mobile/.dart_tool/
/mobile/build/
/mobile/ios/Pods/
/mobile/android/.gradle/

# Sistema
.DS_Store
Thumbs.db
.idea/
.vscode/
```

- [ ] **Step 3: Definir o banco no .env e criar .env.example**

No `backend/.env`, configurar MySQL local (nome `comunidade_ia`). Copiar para `backend/.env.example` com valores vazios:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=comunidade_ia
DB_USERNAME=
DB_PASSWORD=
```

Criar o banco: `mysql -e "CREATE DATABASE IF NOT EXISTS comunidade_ia CHARACTER SET utf8mb4;"`

- [ ] **Step 4: Rodar a suíte de testes padrão para validar o setup**

Run: `cd backend && php artisan test`
Expected: PASS (testes padrão do Laravel verdes).

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "Scaffold do backend Laravel 12 com Sanctum e api.php"
```

---

### Task 2: Migrations, models e factories do domínio

**Files:**
- Modify: `backend/database/migrations/0001_01_01_000000_create_users_table.php`
- Create: migrations para `instrutores`, `categorias`, `cursos`, `modulos`, `aulas`, `progresso_aulas`, `minha_lista`, `comentarios`, `avisos`
- Create: `backend/app/Models/{Instrutor,Categoria,Curso,Modulo,Aula,ProgressoAula,MinhaLista,Comentario,Aviso}.php`
- Modify: `backend/app/Models/User.php`
- Create: factories correspondentes em `backend/database/factories/`
- Test: `backend/tests/Feature/ModeloRelacionamentosTest.php`

**Interfaces:**
- Produces: models Eloquent com relações — `Curso hasMany Modulo`, `Modulo hasMany Aula`, `Curso belongsTo Categoria`, `Curso belongsTo Instrutor`, `User hasMany ProgressoAula`, `Aula hasMany Comentario`. Colunas conforme spec §4. `User` ganha `tem_acesso` (bool), `acesso_expira_em` (date), `role` (string), `phone`.

- [ ] **Step 1: Escrever o teste de relacionamentos (falhando)**

`backend/tests/Feature/ModeloRelacionamentosTest.php`:

```php
<?php
use App\Models\{Categoria, Instrutor, Curso, Modulo, Aula};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('curso pertence a categoria e instrutor e tem modulos com aulas', function () {
    $curso = Curso::factory()
        ->for(Categoria::factory())
        ->for(Instrutor::factory())
        ->create();

    $modulo = Modulo::factory()->for($curso)->create();
    $aula = Aula::factory()->for($modulo)->create();

    expect($curso->categoria)->toBeInstanceOf(Categoria::class);
    expect($curso->instrutor)->toBeInstanceOf(Instrutor::class);
    expect($curso->modulos->first()->id)->toBe($modulo->id);
    expect($modulo->aulas->first()->id)->toBe($aula->id);
});
```

> Observação: este projeto usa PHPUnit por padrão; se optar por Pest, mantenha os arquivos `.php` de teste com a sintaxe acima. Caso use PHPUnit puro, converta para classes `extends TestCase` com métodos `test_*` e asserts equivalentes. Escolha **uma** convenção e mantenha em todo o plano.

- [ ] **Step 2: Rodar o teste para confirmar que falha**

Run: `php artisan test --filter=ModeloRelacionamentos`
Expected: FAIL ("Class App\Models\Curso not found").

- [ ] **Step 3: Criar a migration de `categorias`**

```bash
php artisan make:migration create_categorias_table
```

Conteúdo do `up()`:

```php
Schema::create('categorias', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->string('slug')->unique();
    $table->string('icone')->nullable();
    $table->unsignedInteger('ordem')->default(0);
    $table->timestamps();
});
```

- [ ] **Step 4: Criar a migration de `instrutores`**

```php
Schema::create('instrutores', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->text('bio')->nullable();
    $table->string('foto_url')->nullable();
    $table->timestamps();
});
```

- [ ] **Step 5: Criar a migration de `cursos`**

```php
Schema::create('cursos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
    $table->foreignId('instrutor_id')->nullable()->constrained('instrutores')->nullOnDelete();
    $table->string('titulo');
    $table->string('slug')->unique();
    $table->text('descricao')->nullable();
    $table->string('capa_url')->nullable();
    $table->string('banner_url')->nullable();
    $table->unsignedInteger('duracao_total')->default(0);
    $table->unsignedInteger('ordem')->default(0);
    $table->enum('status', ['rascunho', 'publicado'])->default('rascunho');
    $table->boolean('destaque')->default(false);
    $table->unsignedBigInteger('views')->default(0);
    $table->timestamps();
});
```

- [ ] **Step 6: Criar a migration de `modulos`**

```php
Schema::create('modulos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
    $table->string('titulo');
    $table->unsignedInteger('ordem')->default(0);
    $table->timestamps();
});
```

- [ ] **Step 7: Criar a migration de `aulas`**

```php
Schema::create('aulas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
    $table->string('titulo');
    $table->text('descricao')->nullable();
    $table->string('bunny_library_id')->nullable();
    $table->string('bunny_video_id')->nullable();
    $table->unsignedInteger('duracao')->default(0);
    $table->string('material_url')->nullable();
    $table->unsignedInteger('ordem')->default(0);
    $table->boolean('is_bonus')->default(false);
    $table->dateTime('liberada_em')->nullable();
    $table->unsignedBigInteger('views')->default(0);
    $table->timestamps();
});
```

- [ ] **Step 8: Criar as migrations de `progresso_aulas`, `minha_lista`, `comentarios`, `avisos`**

`progresso_aulas`:

```php
Schema::create('progresso_aulas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
    $table->boolean('concluida')->default(false);
    $table->unsignedInteger('posicao_segundos')->default(0);
    $table->timestamps();
    $table->unique(['user_id', 'aula_id']);
});
```

`minha_lista`:

```php
Schema::create('minha_lista', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['user_id', 'curso_id']);
});
```

`comentarios`:

```php
Schema::create('comentarios', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
    $table->text('texto');
    $table->foreignId('comentario_pai_id')->nullable()->constrained('comentarios')->cascadeOnDelete();
    $table->boolean('aprovado')->default(true);
    $table->timestamps();
});
```

`avisos`:

```php
Schema::create('avisos', function (Blueprint $table) {
    $table->id();
    $table->string('titulo');
    $table->text('corpo');
    $table->string('imagem_url')->nullable();
    $table->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
    $table->dateTime('publicado_em')->nullable();
    $table->timestamps();
});
```

- [ ] **Step 9: Adicionar colunas ao `users` (tem_acesso, acesso_expira_em, role, phone)**

Na migration `create_users_table`, dentro do `Schema::create('users', ...)`, após `email`:

```php
$table->string('phone')->nullable();
$table->enum('role', ['admin', 'aluno'])->default('aluno');
$table->boolean('tem_acesso')->default(false);
$table->date('acesso_expira_em')->nullable();
```

- [ ] **Step 10: Criar os models com relações**

`backend/app/Models/Curso.php`:

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function categoria() { return $this->belongsTo(Categoria::class); }
    public function instrutor() { return $this->belongsTo(Instrutor::class); }
    public function modulos() { return $this->hasMany(Modulo::class)->orderBy('ordem'); }
}
```

`backend/app/Models/Modulo.php`:

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function curso() { return $this->belongsTo(Curso::class); }
    public function aulas() { return $this->hasMany(Aula::class)->orderBy('ordem'); }
}
```

`backend/app/Models/Aula.php`:

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function modulo() { return $this->belongsTo(Modulo::class); }
    public function comentarios() { return $this->hasMany(Comentario::class); }
}
```

`Categoria`, `Instrutor`, `ProgressoAula`, `MinhaLista`, `Comentario`, `Aviso` seguem o mesmo padrão (`protected $guarded = []; use HasFactory;`) com estas relações:
- `Categoria`: `public function cursos() { return $this->hasMany(Curso::class); }`
- `Instrutor`: `public function cursos() { return $this->hasMany(Curso::class); }`
- `ProgressoAula`: `belongsTo(User::class)`, `belongsTo(Aula::class)`; `protected $table = 'progresso_aulas';`
- `MinhaLista`: `belongsTo(User::class)`, `belongsTo(Curso::class)`; `protected $table = 'minha_lista';`
- `Comentario`: `belongsTo(User::class)`, `belongsTo(Aula::class)`, `belongsTo(Comentario::class, 'comentario_pai_id')` como `pai`
- `Aviso`: `belongsTo(User::class, 'autor_id')` como `autor`

- [ ] **Step 11: Atualizar o model User**

Em `backend/app/Models/User.php`, ajustar `$fillable` e casts e adicionar relações:

```php
protected $fillable = ['name', 'email', 'password', 'phone', 'role', 'tem_acesso', 'acesso_expira_em'];

protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'tem_acesso' => 'boolean',
        'acesso_expira_em' => 'date',
    ];
}

public function progressos() { return $this->hasMany(\App\Models\ProgressoAula::class); }
public function minhaLista() { return $this->hasMany(\App\Models\MinhaLista::class); }
```

- [ ] **Step 12: Criar as factories**

Exemplo `backend/database/factories/CursoFactory.php`:

```php
<?php
namespace Database\Factories;
use App\Models\{Categoria, Instrutor};
use Illuminate\Database\Eloquent\Factories\Factory;

class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'instrutor_id' => Instrutor::factory(),
            'titulo' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'descricao' => fake()->paragraph(),
            'status' => 'publicado',
        ];
    }
}
```

Criar `CategoriaFactory` (nome + slug único), `InstrutorFactory` (nome), `ModuloFactory` (titulo + curso_id via factory), `AulaFactory` (titulo + modulo_id via factory). Cada model referencia sua factory por convenção (`Model::factory()`).

- [ ] **Step 13: Rodar as migrations e o teste**

Run: `php artisan migrate:fresh && php artisan test --filter=ModeloRelacionamentos`
Expected: PASS.

- [ ] **Step 14: Commit**

```bash
git add -A
git commit -m "Adiciona migrations, models e factories do dominio"
```

---

### Task 3: Cadastro (register)

**Files:**
- Create: `backend/app/Http/Controllers/Api/AuthController.php`
- Modify: `backend/routes/api.php`
- Test: `backend/tests/Feature/Auth/RegisterTest.php`

**Interfaces:**
- Produces: `POST /api/v1/auth/register` → 201 com `{ user, token }`. Cria usuário com `role=aluno`, `tem_acesso=false`.

- [ ] **Step 1: Escrever o teste (falhando)**

`backend/tests/Feature/Auth/RegisterTest.php`:

```php
<?php
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('cadastro cria usuario sem acesso e retorna token', function () {
    $resp = $this->postJson('/api/v1/auth/register', [
        'name' => 'Pedro Castilho',
        'email' => 'pedro@example.com',
        'phone' => '54999999999',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ]);

    $resp->assertCreated()->assertJsonStructure(['user' => ['id', 'email'], 'token']);
    $this->assertDatabaseHas('users', ['email' => 'pedro@example.com', 'tem_acesso' => false, 'role' => 'aluno']);
});
```

- [ ] **Step 2: Rodar para confirmar que falha**

Run: `php artisan test --filter=RegisterTest`
Expected: FAIL (rota 404).

- [ ] **Step 3: Implementar o controller e a rota**

`AuthController@register`:

```php
public function register(Request $request)
{
    $dados = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users,email'],
        'phone' => ['nullable', 'string', 'max:30'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = User::create($dados);
    $token = $user->createToken('app')->plainTextToken;

    return response()->json(['user' => $user, 'token' => $token], 201);
}
```

Em `routes/api.php`:

```php
use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
});
```

- [ ] **Step 4: Rodar o teste**

Run: `php artisan test --filter=RegisterTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "Adiciona endpoint de cadastro"
```

---

### Task 4: Login e logout

**Files:**
- Modify: `backend/app/Http/Controllers/Api/AuthController.php`
- Modify: `backend/routes/api.php`
- Test: `backend/tests/Feature/Auth/LoginTest.php`

**Interfaces:**
- Consumes: `User` factory, `POST /auth/register`.
- Produces: `POST /api/v1/auth/login` → 200 `{ user, token }` (401 se inválido); `POST /api/v1/auth/logout` (auth:sanctum) → 204.

- [ ] **Step 1: Escrever o teste (falhando)**

`backend/tests/Feature/Auth/LoginTest.php`:

```php
<?php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('login valido retorna token', function () {
    User::factory()->create(['email' => 'a@a.com', 'password' => 'senha12345']);
    $this->postJson('/api/v1/auth/login', ['email' => 'a@a.com', 'password' => 'senha12345'])
        ->assertOk()->assertJsonStructure(['user', 'token']);
});

test('login invalido retorna 401', function () {
    User::factory()->create(['email' => 'a@a.com', 'password' => 'senha12345']);
    $this->postJson('/api/v1/auth/login', ['email' => 'a@a.com', 'password' => 'errada'])
        ->assertUnauthorized();
});
```

- [ ] **Step 2: Rodar para confirmar que falha**

Run: `php artisan test --filter=LoginTest`
Expected: FAIL.

- [ ] **Step 3: Implementar login/logout e rotas**

```php
public function login(Request $request)
{
    $dados = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $dados['email'])->first();
    if (! $user || ! Hash::check($dados['password'], $user->password)) {
        return response()->json(['message' => 'Credenciais invalidas'], 401);
    }

    return response()->json(['user' => $user, 'token' => $user->createToken('app')->plainTextToken]);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    return response()->noContent();
}
```

Rotas:

```php
Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);
```

Imports no topo do controller: `use Illuminate\Support\Facades\Hash;`.

- [ ] **Step 4: Rodar o teste**

Run: `php artisan test --filter=LoginTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "Adiciona login e logout"
```

---

### Task 5: Perfil — ver, atualizar, trocar senha, excluir (LGPD)

**Files:**
- Create: `backend/app/Http/Controllers/Api/PerfilController.php`
- Modify: `backend/routes/api.php`
- Test: `backend/tests/Feature/Auth/PerfilTest.php`

**Interfaces:**
- Consumes: `auth:sanctum`, `User` factory.
- Produces: `GET /api/v1/me`, `PUT /api/v1/me`, `PUT /api/v1/me/password`, `DELETE /api/v1/me` (todos sob `auth:sanctum`).

- [ ] **Step 1: Escrever os testes (falhando)**

`backend/tests/Feature/Auth/PerfilTest.php`:

```php
<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('me retorna o usuario autenticado', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->getJson('/api/v1/me')->assertOk()->assertJsonPath('id', $user->id);
});

test('troca de senha exige senha atual correta', function () {
    $user = User::factory()->create(['password' => 'senha12345']);
    $this->actingAs($user)->putJson('/api/v1/me/password', [
        'senha_atual' => 'senha12345',
        'password' => 'novasenha123',
        'password_confirmation' => 'novasenha123',
    ])->assertOk();
    expect(Hash::check('novasenha123', $user->fresh()->password))->toBeTrue();
});

test('excluir conta remove o usuario', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->deleteJson('/api/v1/me')->assertNoContent();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
```

- [ ] **Step 2: Rodar para confirmar que falha**

Run: `php artisan test --filter=PerfilTest`
Expected: FAIL.

- [ ] **Step 3: Implementar o controller e as rotas**

`PerfilController`:

```php
public function show(Request $request) { return response()->json($request->user()); }

public function update(Request $request)
{
    $dados = $request->validate([
        'name' => ['sometimes', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:30'],
    ]);
    $request->user()->update($dados);
    return response()->json($request->user());
}

public function updatePassword(Request $request)
{
    $dados = $request->validate([
        'senha_atual' => ['required', 'string'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);
    if (! Hash::check($dados['senha_atual'], $request->user()->password)) {
        return response()->json(['message' => 'Senha atual incorreta'], 422);
    }
    $request->user()->update(['password' => $dados['password']]);
    return response()->noContent();
}

public function destroy(Request $request)
{
    $request->user()->delete();
    return response()->noContent();
}
```

Rotas (sob `auth:sanctum`, dentro do grupo `v1`):

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [PerfilController::class, 'show']);
    Route::put('me', [PerfilController::class, 'update']);
    Route::put('me/password', [PerfilController::class, 'updatePassword']);
    Route::delete('me', [PerfilController::class, 'destroy']);
});
```

> Nota: `updatePassword` retorna 204, mas o teste espera 200 (`assertOk`). Ajustar o teste para `assertNoContent()` OU retornar `response()->json(['ok' => true])`. Escolher retornar `response()->json(['ok' => true])` para bater com o teste.

- [ ] **Step 4: Rodar os testes**

Run: `php artisan test --filter=PerfilTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "Adiciona perfil: ver, atualizar, trocar senha e excluir conta"
```

---

### Task 6: Middleware de controle de acesso (`acesso.ativo`)

**Files:**
- Create: `backend/app/Http/Middleware/AcessoAtivo.php`
- Modify: `backend/bootstrap/app.php`
- Modify: `backend/routes/api.php` (rota de exemplo protegida)
- Test: `backend/tests/Feature/AcessoAtivoTest.php`

**Interfaces:**
- Consumes: `auth:sanctum`, `User` com `tem_acesso` e `acesso_expira_em`.
- Produces: alias de middleware `acesso.ativo`. Bloqueia (403) usuários sem `tem_acesso` ou com acesso expirado; libera quando `tem_acesso=true` e (sem data OU `acesso_expira_em >= hoje`).

- [ ] **Step 1: Escrever o teste (falhando)**

`backend/tests/Feature/AcessoAtivoTest.php`:

```php
<?php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('sem acesso recebe 403 em rota protegida', function () {
    $user = User::factory()->create(['tem_acesso' => false]);
    $this->actingAs($user)->getJson('/api/v1/ping-conteudo')->assertForbidden();
});

test('com acesso ativo entra na rota protegida', function () {
    $user = User::factory()->create(['tem_acesso' => true, 'acesso_expira_em' => now()->addYear()]);
    $this->actingAs($user)->getJson('/api/v1/ping-conteudo')->assertOk();
});

test('acesso expirado recebe 403', function () {
    $user = User::factory()->create(['tem_acesso' => true, 'acesso_expira_em' => now()->subDay()]);
    $this->actingAs($user)->getJson('/api/v1/ping-conteudo')->assertForbidden();
});
```

- [ ] **Step 2: Rodar para confirmar que falha**

Run: `php artisan test --filter=AcessoAtivoTest`
Expected: FAIL.

- [ ] **Step 3: Criar o middleware**

`backend/app/Http/Middleware/AcessoAtivo.php`:

```php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class AcessoAtivo
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $expirado = $user && $user->acesso_expira_em && $user->acesso_expira_em->isPast();
        if (! $user || ! $user->tem_acesso || $expirado) {
            return response()->json(['message' => 'Acesso inativo'], 403);
        }
        return $next($request);
    }
}
```

- [ ] **Step 4: Registrar o alias e a rota de exemplo**

Em `backend/bootstrap/app.php`, dentro de `->withMiddleware(function (Middleware $middleware) { ... })`:

```php
$middleware->alias(['acesso.ativo' => \App\Http\Middleware\AcessoAtivo::class]);
```

Em `routes/api.php` (grupo `v1`):

```php
Route::middleware(['auth:sanctum', 'acesso.ativo'])->get('ping-conteudo', fn () => response()->json(['ok' => true]));
```

- [ ] **Step 5: Rodar o teste**

Run: `php artisan test --filter=AcessoAtivoTest`
Expected: PASS.

- [ ] **Step 6: Rodar a suíte inteira**

Run: `php artisan test`
Expected: PASS (tudo verde).

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "Adiciona middleware de controle de acesso ao conteudo"
```

---

## Self-Review (cobertura vs spec)

- **§4 Modelo de dados:** todas as tabelas criadas na Task 2 ✔
- **§5 Controle de acesso:** flag `tem_acesso`/`acesso_expira_em` + middleware `acesso.ativo` (Tasks 2, 6) ✔
- **§6 Auth/Perfil:** register, login, logout, me, update, password, delete (Tasks 3–5) ✔
- **§6 Conteúdo/Comunidade endpoints:** **Plano 2** (fora deste plano) — home, cursos, aulas, progresso, em-alta, minha-lista, comentarios, avisos, download Bunny.
- **§9 Admin, §7/§8 Telas, §10 Bunny:** Planos 3, 4, 5.

Sem placeholders. Convenção de teste única (Pest-style `.php`; se PHPUnit, converter conforme nota da Task 2). Assinaturas consistentes entre tasks.

## Notas de execução (2026-08-24 — CONCLUÍDO)

Plano 1 implementado e no repositório. 17 testes verdes.

Desvios em relação ao spec, por causa do ambiente da máquina:
- **PHP 8.3.30** (o 8.4 do spec não está instalado; 8.5.7 existe mas é muito recente). Totalmente suportado pelo Laravel.
- **Laravel 13.26.1** (o `composer create-project` trouxe o stable atual; o spec citava 12). Compatível com o que foi construído.
- **SQLite** no ambiente de dev/testes (não há MySQL na máquina). **MySQL/MariaDB continua o alvo de produção** — as migrations são portáveis.
- Laravel 13 usa atributos (`#[Fillable(...)]`) no model User em vez de `$fillable`; o código foi adaptado.
- Campos de acesso adicionados ao `users` via **nova migration** (não editando a existente), conforme regra do projeto.
- Testes escritos em **PHPUnit** (framework padrão do projeto), não Pest.
- Comando: PHP e Composer chamados por caminho absoluto (`/c/php83/php.exe`, `/c/composer/composer.phar`), pois não estão no PATH deste shell.

## Próximos planos

- **Plano 2:** API de conteúdo + comunidade (endpoints §6 restantes + integração Bunny + download).
- **Plano 3:** Admin Filament (§9).
- **Plano 4:** Web Vue/Inertia (§8).
- **Plano 5:** App Flutter + offline (§7, §10).
