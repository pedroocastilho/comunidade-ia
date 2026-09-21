<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SenhaPadraoDefinida;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

/**
 * Primeiro acesso de quem entrou com a senha padrao do checkout:
 * define a senha propria antes de navegar na plataforma.
 */
class PrimeiroAcessoController extends Controller
{
    public function formulario(Request $request)
    {
        // Quem ja tem senha propria nao precisa desta tela
        if ($request->session()->get('senha_padrao') !== true) {
            return redirect()->route('home');
        }

        return Inertia::render('Auth/PrimeiraSenha', [
            'apelido' => $request->user()->apelido ?? $request->user()->name,
        ]);
    }

    public function salvar(Request $request)
    {
        $dados = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($dados['password'] === SenhaPadraoDefinida::SENHA_PADRAO) {
            return back()->withErrors(['password' => 'Escolha uma senha diferente da senha padrão.']);
        }

        $request->user()->update(['password' => $dados['password']]);
        $request->session()->put('senha_padrao', false);

        return redirect()->route('home');
    }
}
