<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuraScore extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'scores_dimensoes' => 'array',
        'padroes' => 'array',
        'calculado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dimensaoPrioritaria()
    {
        return $this->belongsTo(Dimensao::class, 'dimensao_prioritaria_id');
    }
}
