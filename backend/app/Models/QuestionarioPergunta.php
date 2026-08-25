<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionarioPergunta extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'opcoes' => 'array',
        'obrigatoria' => 'boolean',
        'alimenta_memoria' => 'boolean',
    ];

    public function dimensao()
    {
        return $this->belongsTo(Dimensao::class);
    }

    public function respostas()
    {
        return $this->hasMany(QuestionarioResposta::class, 'pergunta_id');
    }
}
