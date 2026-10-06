<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\Price;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchMetalsPrices extends Command
{
    protected $signature = 'prices:fetch-metals';
    protected $description = 'Obtener precios de metales desde GoldAPI';

    public function handle()
    {
        $metals = Asset::whereIn('symbol', ['XAU', 'XAG'])
            ->where('active', true)
            ->get();

        if ($metals->isEmpty()) {
            $this->error('No hay metales configurados');
            return 1;
        }

        // Obtener tipo de cambio
        try {
            $mxnRate = $this->getUsdToMxn();
        } catch (\Exception $e) {
            Log::warning('No se pudo obtener tipo de cambio', [
                'error' => $e->getMessage()
            ]);
            $this->error('Error obteniendo tipo de cambio - se mantienen precios anteriores');
            return 1;
        }

        foreach ($metals as $asset) {
            try {
                $this->fetchMetal($asset, $mxnRate);
                $this->info("✓ {$asset->symbol} actualizado");
            } catch (\Exception $e) {
                Log::warning("Error obteniendo precio de {$asset->symbol}", [
                    'error' => $e->getMessage()
                ]);
                $this->warn("✗ {$asset->symbol}: {$e->getMessage()} - se mantiene precio anterior");
            }
        }

        return 0;
    }

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
            throw new \Exception('Banxico no respondió');
        }

        $data = $response->json();
        
        if (!isset($data['bmx']['series'][0]['datos'][0]['dato'])) {
            throw new \Exception('Formato de respuesta de Banxico incorrecto');
        }

        return (float) $data['bmx']['series'][0]['datos'][0]['dato'];
    }

    private function fetchMetal(Asset $asset, float $mxnRate): void
    {
        $response = Http::timeout(60)
            ->retry(3, 2000)
            ->withHeaders([
                'x-access-token' => config('services.goldapi.key'),
            ])->get("https://www.goldapi.io/api/{$asset->symbol}/USD");

        if (!$response->ok()) {
            throw new \Exception("GoldAPI no respondió (HTTP {$response->status()})");
        }

        $data = $response->json();
        
        if (!isset($data['price'])) {
            throw new \Exception('Respuesta de GoldAPI sin precio');
        }

        $usdPerOunce = $data['price'];
        
        if ($usdPerOunce <= 0) {
            throw new \Exception('Precio inválido recibido (<=0)');
        }

        // 1 onza troy = 31.1035 gramos
        $usdPerGram = $usdPerOunce / 31.1035;

        Price::create([
            'asset_id'   => $asset->id,
            'price_usd'  => $usdPerGram,
            'price_mxn'  => $usdPerGram * $mxnRate,
            'source'     => 'GoldAPI + Banxico',
            'fetched_at' => now(),
        ]);
    }
}
