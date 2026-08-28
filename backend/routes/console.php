<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Movimento organico do Circulo: perfis ficticios publicam, curtem e comentam
// ao longo do dia. Exige o cron do Laravel no servidor (* * * * * php artisan schedule:run).
Schedule::command('circulo:movimentar')->hourly()->withoutOverlapping();
