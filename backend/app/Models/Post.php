<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'fixado' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reacoes()
    {
        return $this->hasMany(PostReacao::class);
    }

    public function comentarios()
    {
        return $this->hasMany(PostComentario::class)->where('status', 'publicado')->oldest('id');
    }
}
