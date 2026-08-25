<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaConfiguracao extends Model
{
    protected $table = 'ia_configuracoes';

    protected $guarded = [];

    /**
     * Busca o valor de uma chave de configuracao, com fallback.
     */
    public static function valor(string $chave, ?string $padrao = null): ?string
    {
        return static::where('chave', $chave)->value('valor') ?? $padrao;
    }
}
