<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JornadaTemplateDia extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'equivalentes' => 'array',
        'variante_curta' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(JornadaTemplate::class, 'template_id');
    }

    public function ritualAudio()
    {
        return $this->belongsTo(Audio::class, 'ritual_audio_id');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }
}
