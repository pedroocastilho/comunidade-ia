<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoAnalytics extends Model
{
    protected $table = 'eventos_analytics';

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'propriedades' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
