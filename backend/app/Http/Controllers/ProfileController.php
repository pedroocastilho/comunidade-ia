<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $gamificacao = app(\App\Services\GamificacaoService::class);
        $conquistadas = $user->conquistas()->get()->keyBy('slug');

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'evolucao' => [
                ...$gamificacao->progressoNivel($user->xp ?? 0),
                'streak' => $gamificacao->streak($user),
            ],
            'conquistas' => \App\Models\Conquista::orderBy('ordem')->get()
                ->map(fn ($c) => [
                    'slug' => $c->slug,
                    'nome' => $c->nome,
                    'descricao' => $c->descricao,
                    'icone' => $c->icone,
                    'conquistado_em' => $conquistadas[$c->slug]?->pivot?->conquistado_em ?? null,
                ]),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
