<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReacao extends Model
{
    protected $table = 'post_reacoes';

    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
