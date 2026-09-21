<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class PerfilController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->only(['id', 'name', 'apelido', 'email', 'phone', 'tem_acesso', 'assinatura_status']));
    }

    public function update(Request $request)
    {
        $dados = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $request->user()->update($dados);

        return response()->json($request->user()->only(['id', 'name', 'apelido', 'email', 'phone', 'tem_acesso', 'assinatura_status']));
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

        // Troca de senha invalida os demais tokens (sessoes de outros aparelhos)
        $atual = $request->user()->currentAccessToken();
        $request->user()->tokens()
            ->when($atual instanceof PersonalAccessToken, fn ($q) => $q->whereKeyNot($atual->id))
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        // Mesma exigencia do fluxo web: confirmar a senha antes de excluir
        $dados = $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($dados['password'], $request->user()->password)) {
            return response()->json(['message' => 'Senha incorreta'], 422);
        }

        $request->user()->tokens()->delete();
        $request->user()->delete();

        return response()->noContent();
    }
}
