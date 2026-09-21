<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AudioWebController;
use App\Http\Controllers\Web\AuraChatController;
use App\Http\Controllers\Web\CirculoController;
use App\Http\Controllers\Web\JornadaWebController;
use App\Http\Controllers\Web\MetaController;
use App\Http\Controllers\Web\OnboardingController;
use App\Http\Controllers\Web\PainelController;
use App\Http\Controllers\Web\PrimeiroAcessoController;
use App\Http\Controllers\Web\ReavaliacaoController;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Paginas legais (publicas)
Route::get('/termos', fn () => Inertia::render('Legal/Termos'))->name('termos');
Route::get('/privacidade', fn () => Inertia::render('Legal/Privacidade'))->name('privacidade');

// Troca de idioma (pt | es): sessao + conta do usuario (persiste entre dispositivos).
Route::post('/idioma', function () {
    $locale = request('locale');
    if (in_array($locale, ['pt', 'es'], true)) {
        session(['locale' => $locale]);
        auth()->user()?->update(['locale' => $locale]);
    }

    return back();
})->name('idioma');

Route::get('/sem-acesso', function () {
    app(AnalyticsService::class)->registrar('subscription_blocked_view', auth()->user());

    return Inertia::render('App/SemAcesso', ['checkout_url' => config('circulo.checkout_url')]);
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
    Route::get('/reavaliacao', [ReavaliacaoController::class, 'formulario'])->name('reavaliacao');
    Route::post('/reavaliacao', [ReavaliacaoController::class, 'salvar'])->name('reavaliacao.salvar');
    Route::post('/jornada/atividade', [JornadaWebController::class, 'concluirAtividade'])->name('jornada.atividade');
    Route::post('/checkin', [JornadaWebController::class, 'checkin'])->name('checkin');
    Route::get('/aura', [AuraChatController::class, 'index'])->name('aura');
    Route::post('/aura/mensagem', [AuraChatController::class, 'mensagem'])
        ->middleware('throttle:30,10')->name('aura.mensagem');
    Route::get('/circulo', [CirculoController::class, 'index'])->name('circulo');
    Route::post('/circulo/posts', [CirculoController::class, 'publicar'])
        ->middleware('throttle:10,10')->name('circulo.publicar');
    Route::post('/circulo/posts/{post}/reagir', [CirculoController::class, 'reagir'])->name('circulo.reagir');
    Route::post('/circulo/posts/{post}/comentar', [CirculoController::class, 'comentar'])
        ->middleware('throttle:20,10')->name('circulo.comentar');
    Route::post('/circulo/denunciar', [CirculoController::class, 'denunciar'])->name('circulo.denunciar');
    Route::get('/metas', [MetaController::class, 'index'])->name('metas');
    Route::post('/metas', [MetaController::class, 'store'])->name('metas.store');
    Route::patch('/metas/{meta}', [MetaController::class, 'update'])->name('metas.update');
    Route::delete('/metas/{meta}', [MetaController::class, 'destroy'])->name('metas.destroy');
    Route::get('/audios', [AudioWebController::class, 'index'])->name('audios');
    Route::get('/audios/{audio}', [AudioWebController::class, 'player'])->name('audio');
    Route::post('/audios/{audio}/progresso', [AudioWebController::class, 'progresso'])->name('audio.progresso');
    Route::get('/cursos', [PainelController::class, 'cursos'])->name('cursos');
    Route::get('/cursos/{slug}', [PainelController::class, 'curso'])->name('curso');
    Route::get('/aulas/{aula}', [PainelController::class, 'aula'])->name('aula');
    Route::post('/aulas/{aula}/concluir', [PainelController::class, 'concluirAula'])->name('aula.concluir');
});

// Primeiro acesso: definir senha propria (quem veio do checkout com senha padrao)
Route::middleware('auth')->group(function () {
    Route::get('/primeiro-acesso/senha', [PrimeiroAcessoController::class, 'formulario'])->name('senha.definir');
    Route::post('/primeiro-acesso/senha', [PrimeiroAcessoController::class, 'salvar'])->middleware('throttle:10,1')->name('senha.salvar');
});

// Perfil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
