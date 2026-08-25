<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionarioResposta extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pergunta()
    {
        return $this->belongsTo(QuestionarioPergunta::class, 'pergunta_id');
    }
}
