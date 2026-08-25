<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AudioWebController;
use App\Http\Controllers\Web\AuraChatController;
use App\Http\Controllers\Web\JornadaWebController;
use App\Http\Controllers\Web\OnboardingController;
use App\Http\Controllers\Web\PainelController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Troca de idioma (pt | es), guardada na sessao.
Route::post('/idioma', function () {
    $locale = request('locale');
    if (in_array($locale, ['pt', 'es'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('idioma');

Route::get('/sem-acesso', function () {
    app(\App\Services\AnalyticsService::class)->registrar('subscription_blocked_view', auth()->user());

    return Inertia::render('App/SemAcesso');
})->middleware('auth')->name('sem-acesso');

// Compatibilidade: Breeze referencia a rota "dashboard"; mandamos para a home.
Route::get('/dashboard', fn () => redirect()->route('home'))
    ->middleware('auth')->name('dashboard');

// Onboarding e Aura Score: exigem acesso, mas ficam fora do gate de onboarding
Route::middleware(['auth', 'acesso.web'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'questionario'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'salvar'])->name('onboarding.salvar');
    Route::get('/aura-score', [OnboardingController::class, 'score'])->name('aura-score');
});

// Area logada com assinatura ativa
Route::middleware(['auth', 'acesso.web', 'onboarding.completo'])->group(function () {
    // Home diaria do Circulo Aura (o catalogo antigo segue em /cursos)
    Route::get('/inicio', [JornadaWebController::class, 'home'])->name('home');
    Route::get('/jornada', [JornadaWebController::class, 'jornada'])->name('jornada');
    Route::post('/jornada/nova', [JornadaWebController::class, 'novaJornada'])->name('jornada.nova');
    Route::get('/reavaliacao', [\App\Http\Controllers\Web\ReavaliacaoController::class, 'formulario'])->name('reavaliacao');
    Route::post('/reavaliacao', [\App\Http\Controllers\Web\ReavaliacaoController::class, 'salvar'])->name('reavaliacao.salvar');
    Route::post('/jornada/atividade', [JornadaWebController::class, 'concluirAtividade'])->name('jornada.atividade');
    Route::post('/checkin', [JornadaWebController::class, 'checkin'])->name('checkin');
    Route::get('/aura', [AuraChatController::class, 'index'])->name('aura');
    Route::post('/aura/mensagem', [AuraChatController::class, 'mensagem'])
        ->middleware('throttle:30,10')->name('aura.mensagem');
    Route::get('/circulo', [\App\Http\Controllers\Web\CirculoController::class, 'index'])->name('circulo');
    Route::post('/circulo/posts', [\App\Http\Controllers\Web\CirculoController::class, 'publicar'])
        ->middleware('throttle:10,10')->name('circulo.publicar');
    Route::post('/circulo/posts/{post}/reagir', [\App\Http\Controllers\Web\CirculoController::class, 'reagir'])->name('circulo.reagir');
    Route::post('/circulo/posts/{post}/comentar', [\App\Http\Controllers\Web\CirculoController::class, 'comentar'])
        ->middleware('throttle:20,10')->name('circulo.comentar');
    Route::post('/circulo/denunciar', [\App\Http\Controllers\Web\CirculoController::class, 'denunciar'])->name('circulo.denunciar');
    Route::get('/audios', [AudioWebController::class, 'index'])->name('audios');
    Route::get('/audios/{audio}', [AudioWebController::class, 'player'])->name('audio');
    Route::post('/audios/{audio}/progresso', [AudioWebController::class, 'progresso'])->name('audio.progresso');
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
