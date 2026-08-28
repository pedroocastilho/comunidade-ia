<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Meta pessoal com prazo. "Vencida" = prazo passou e nao foi concluida.
 */
class Meta extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'prazo' => 'date',
        'concluida_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEmAndamento($query)
    {
        return $query->whereNull('concluida_em')->whereDate('prazo', '>=', today());
    }

    public function scopeConcluidas($query)
    {
        return $query->whereNotNull('concluida_em');
    }

    public function scopeVencidas($query)
    {
        return $query->whereNull('concluida_em')->whereDate('prazo', '<', today());
    }

    /** Situacao calculada para a interface: andamento | concluida | vencida */
    public function situacao(): string
    {
        if ($this->concluida_em) {
            return 'concluida';
        }

        return $this->prazo->lt(today()) ? 'vencida' : 'andamento';
    }
}
