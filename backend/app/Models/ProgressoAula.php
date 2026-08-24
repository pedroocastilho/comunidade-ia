<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressoAula extends Model
{
    use HasFactory;

    protected $table = 'progresso_aulas';

    protected $guarded = [];

    protected $casts = [
        'concluida' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }
}
