<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denuncia extends Model
{
    protected $guarded = [];

    protected $casts = [
        'resolvida' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
