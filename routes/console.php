<?php

use App\Jobs\FetchBanxicoRate;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Criptomonedas: cada 5 minutos (UNA sola llamada para todas)
Schedule::command('prices:fetch-crypto')
    ->everyFiveMinutes();

// Metales (Oro y Plata) + tipo de cambio: 2 veces al día
Schedule::command('prices:fetch-metals')
    ->twiceDaily(9, 18);

    // Dólar: 2 veces al día
Schedule::command('prices:fetch-fiat')
    ->twiceDaily(7,16);