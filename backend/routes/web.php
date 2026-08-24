<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\PainelController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Route::get('/sem-acesso', fn () => Inertia::render('App/SemAcesso'))
    ->middleware('auth')->name('sem-acesso');

// Compatibilidade: Breeze referencia a rota "dashboard"; mandamos para a home.
Route::get('/dashboard', fn () => redirect()->route('home'))
    ->middleware('auth')->name('dashboard');

// Area logada com assinatura ativa
Route::middleware(['auth', 'acesso.web'])->group(function () {
    Route::get('/inicio', [PainelController::class, 'home'])->name('home');
    Route::get('/cursos', [PainelController::class, 'cursos'])->name('cursos');
    Route::get('/cursos/{slug}', [PainelController::class, 'curso'])->name('curso');
    Route::get('/aulas/{aula}', [PainelController::class, 'aula'])->name('aula');
    Route::post('/aulas/{aula}/concluir', [PainelController::class, 'concluirAula'])->name('aula.concluir');
});

// Perfil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
