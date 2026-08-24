<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'destaque' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function instrutor()
    {
        return $this->belongsTo(Instrutor::class);
    }

    public function modulos()
    {
        return $this->hasMany(Modulo::class)->orderBy('ordem');
    }
}
