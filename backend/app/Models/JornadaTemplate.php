<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JornadaTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function dimensao()
    {
        return $this->belongsTo(Dimensao::class);
    }

    public function dias()
    {
        return $this->hasMany(JornadaTemplateDia::class, 'template_id')->orderBy('dia');
    }
}
