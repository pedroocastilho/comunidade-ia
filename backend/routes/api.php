<?php

use App\Http\Controllers\Api\AulaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvisoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\CursoController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MinhaListaController;
use App\Http\Controllers\Api\PerfilController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Autenticacao publica
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // Autenticado (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Perfil
        Route::get('me', [PerfilController::class, 'show']);
        Route::put('me', [PerfilController::class, 'update']);
        Route::put('me/password', [PerfilController::class, 'updatePassword']);
        Route::delete('me', [PerfilController::class, 'destroy']);

        // Conteudo e comunidade (exige assinatura ativa)
        Route::middleware('acesso.ativo')->group(function () {
            Route::get('home', [HomeController::class, 'index']);
            Route::get('categorias', [CategoriaController::class, 'index']);
            Route::get('em-alta', [CursoController::class, 'emAlta']);

            Route::get('cursos', [CursoController::class, 'index']);
            Route::get('cursos/{slug}', [CursoController::class, 'show']);

            Route::get('aulas/{aula}', [AulaController::class, 'show']);
            Route::post('aulas/{aula}/concluir', [AulaController::class, 'concluir']);
            Route::put('aulas/{aula}/progresso', [AulaController::class, 'progresso']);
            Route::get('aulas/{aula}/download', [AulaController::class, 'download']);

            Route::get('minha-lista', [MinhaListaController::class, 'index']);
            Route::post('minha-lista/{curso}', [MinhaListaController::class, 'store']);
            Route::delete('minha-lista/{curso}', [MinhaListaController::class, 'destroy']);

            Route::get('aulas/{aula}/comentarios', [ComentarioController::class, 'index']);
            Route::post('aulas/{aula}/comentarios', [ComentarioController::class, 'store']);
            Route::delete('comentarios/{comentario}', [ComentarioController::class, 'destroy']);

            Route::get('avisos', [AvisoController::class, 'index']);
        });
    });
});

// Webhook de pagamento (assinatura recorrente) — token secreto por header, sem auth de usuario
Route::post('/webhooks/pagamento/{plataforma}', [\App\Http\Controllers\Api\WebhookPagamentoController::class, 'receber'])
    ->name('webhooks.pagamento');
