<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'iniciada_em' => 'datetime',
        'ultimo_avanco_em' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(JornadaTemplate::class, 'template_id');
    }

    public function dias()
    {
        return $this->hasMany(JornadaDia::class)->orderBy('dia');
    }
}
