<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SenhaPadraoDefinida;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        abort_unless(config('circulo.cadastro_aberto'), 403);

        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($dados);
        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'apelido', 'email', 'phone']),
            'token' => $token,
        ], 201);
    }

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

        // Comprador ainda com a senha padrao do checkout: obriga a definir a
        // senha propria pelo site antes de liberar token de API (evita tomada
        // de conta por quem conhece o e-mail do comprador).
        if (Hash::check(SenhaPadraoDefinida::SENHA_PADRAO, $user->password)) {
            return response()->json([
                'message' => 'Defina sua senha pessoal acessando '.config('app.url').' antes de usar o aplicativo.',
            ], 403);
        }

        return response()->json([
            'user' => $user->only(['id', 'name', 'apelido', 'email', 'phone']),
            'token' => $user->createToken('app')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        // currentAccessToken() e um TransientToken quando nao ha token real
        // (ex.: sessao web ou actingAs em testes) - so revoga token persistido.
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->noContent();
    }
}
