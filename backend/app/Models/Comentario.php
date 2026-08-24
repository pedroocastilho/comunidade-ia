<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'aprovado' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function pai()
    {
        return $this->belongsTo(Comentario::class, 'comentario_pai_id');
    }

    public function respostas()
    {
        return $this->hasMany(Comentario::class, 'comentario_pai_id');
    }
}
