<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookPagamento extends Model
{
    protected $table = 'webhooks_pagamento';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'processado' => 'boolean',
    ];
}
