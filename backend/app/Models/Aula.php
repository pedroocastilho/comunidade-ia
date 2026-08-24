<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_bonus' => 'boolean',
        'liberada_em' => 'datetime',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
}
