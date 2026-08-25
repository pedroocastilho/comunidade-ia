<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuraMensagem extends Model
{
    use HasFactory;

    protected $table = 'aura_mensagens';

    protected $guarded = [];

    public function conversa()
    {
        return $this->belongsTo(AuraConversa::class, 'conversa_id');
    }
}
