<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JornadaDia extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'ritual_concluido' => 'boolean',
        'aula_concluida' => 'boolean',
        'acao_concluida' => 'boolean',
        'adaptado_por_ia' => 'boolean',
        'origem_adaptacao' => 'array',
        'concluido_em' => 'datetime',
    ];

    public function jornada()
    {
        return $this->belongsTo(Jornada::class);
    }

    public function ritualAudio()
    {
        return $this->belongsTo(Audio::class, 'ritual_audio_id');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }
}
