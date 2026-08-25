<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoAudio extends Model
{
    protected $table = 'progresso_audios';

    protected $guarded = [];

    protected $casts = [
        'concluido' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function audio()
    {
        return $this->belongsTo(Audio::class);
    }
}
