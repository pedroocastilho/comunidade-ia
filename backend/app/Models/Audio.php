<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    use HasFactory;

    protected $table = 'audios';

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'premium' => 'boolean',
    ];

    public function progressos()
    {
        return $this->hasMany(ProgressoAudio::class);
    }
}
