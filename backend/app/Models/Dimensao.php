<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dimensao extends Model
{
    protected $table = 'dimensoes';

    protected $guarded = [];

    public function jornadaTemplates()
    {
        return $this->hasMany(JornadaTemplate::class);
    }
}
