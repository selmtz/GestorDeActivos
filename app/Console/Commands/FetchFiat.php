<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Price;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchFiat extends Command
{
    // ESTA es la firma que Laravel busca
    protected $signature = 'prices:fetch-fiat';

    protected $description = 'Obtener precio del Dólar (USD) desde Banxico';

    public function handle()
    {
        // 1. Buscamos si existe el activo USD
        $asset = Asset::where('symbol', 'USD')->first();

        if (!$asset || !$asset->active) {
            $this->info('El activo USD no existe o no está activo. Saltando...');
            return 0;
        }

        $this->info('Consultando Banxico...');

        try {
            // 2. Obtenemos el precio
            $mxnRate = $this->getUsdToMxn();

            // 3. Guardamos en la BD
            Price::create([
                'asset_id'   => $asset->id,
                'price_usd'  => 1.0,      // 1 USD = 1 USD
                'price_mxn'  => $mxnRate, // Valor de Banxico
                'source'     => 'Banxico',
                'fetched_at' => now(),
            ]);

            $this->info("✓ USD actualizado: $mxnRate MXN");

        } catch (\Exception $e) {
            $this->error('Error actualizando USD: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    // Función auxiliar para conectar a Banxico
    private function getUsdToMxn(): float
    {
        $response = Http::timeout(60)
            ->retry(3, 2000)
            ->withHeaders([
                'Bmx-Token' => config('services.banxico.token'),
            ])->get(
                'https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno'
            );

        if (!$response->ok()) {
            throw new \Exception('Banxico no responde');
        }

        return (float) $response->json()['bmx']['series'][0]['datos'][0]['dato'];
    }
}
