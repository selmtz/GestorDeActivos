<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchBanxicoRate implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(60)
                ->retry(3, 2000)
                ->withHeaders([
                    'Bmx-Token' => config('services.banxico.token'),
                ])->get(
                    'https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno'
                );

            if (!$response->ok()){
                throw new \Exception('Banxico no responde');
            }

            $rate = (float) $response->json()['bmx']['series'][0]['datos'][0]['dato'];

            //mantener en cache por 1 hora
            Cache::put('usd_to_mxn', $rate, now()->addHour());

            Log::info('Tipo de cambio actualizado: $rate MXN');
        } catch (\Exception $e) {
            Log::error('Error obteniendo tipo de cambio: ' . $e->getMessage());
        }
    }
}
