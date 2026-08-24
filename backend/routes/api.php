<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PerfilController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Autenticacao publica
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Autenticado (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Perfil
        Route::get('me', [PerfilController::class, 'show']);
        Route::put('me', [PerfilController::class, 'update']);
        Route::put('me/password', [PerfilController::class, 'updatePassword']);
        Route::delete('me', [PerfilController::class, 'destroy']);

        // Rota de conteudo protegida por acesso ativo (exemplo/smoke test)
        Route::middleware('acesso.ativo')->group(function () {
            Route::get('ping-conteudo', fn () => response()->json(['ok' => true]));
        });
    });
});
