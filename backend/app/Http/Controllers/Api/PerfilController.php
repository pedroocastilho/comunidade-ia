<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

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

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $request->user()->delete();

        return response()->noContent();
    }
}
