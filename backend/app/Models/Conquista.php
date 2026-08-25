<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conquista extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('conquistado_em');
    }
}
