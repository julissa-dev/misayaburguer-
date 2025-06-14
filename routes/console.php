<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // 👈 Importante

// Comando ejemplo que viene por defecto
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 👇 Aquí programas tu comando de checkouts
Schedule::command('checkouts:cancelar-expirados')->everyFiveMinutes();
