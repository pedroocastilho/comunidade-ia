<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function audio()
    {
        return $this->belongsTo(Audio::class);
    }
}
