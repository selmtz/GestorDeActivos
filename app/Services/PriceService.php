<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Price;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PriceService
{
    public function fetchAndStore(): array
    {
        $assets = Asset::where('active', true)->get();
        $results = [];

        foreach ($assets as $asset) {
            try {
                match ($asset->symbol) {
                    'BTC' => $this->fetchBitcoin($asset),
                    'XAU' => $this->fetchMetal($asset, 'XAU'),
                    'XAG' => $this->fetchMetal($asset, 'XAG'),
                    'USD' => $this->fetchUsd($asset),
                    default => null,
                };
                $results[$asset->symbol] = 'OK';
            } catch (\Exception $e) {
                $results[$asset->symbol] = 'ERROR: ' . $e->getMessage();
                Log::error("Error fetching {$asset->symbol}: " . $e->getMessage());
            }
        }

        return $results;
    }

    private function usdToMxn(): float
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::timeout(60)
            ->retry(3, 2000) // 3 reintentos con 1 segundo entre cada uno
            ->withHeaders([
                'Bmx-Token' => config('services.banxico.token'),
            ])->get(
                'https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno'
            );

        if (!$response->ok()) {
            throw new \Exception('No se pudo obtener el tipo de cambio');
        }

        return (float) $response
            ->json()['bmx']['series'][0]['datos'][0]['dato'];
    }

    private function fetchBitcoin(Asset $asset): void
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::timeout(60)
            ->retry(3, 2000) // 3 reintentos
            ->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin',
                'vs_currencies' => 'usd,mxn',
            ]);

        if (!$response->ok()) {
            throw new \Exception('CoinGecko no respondió');
        }

        $data = $response->json()['bitcoin'];

        Price::create([
            'asset_id'   => $asset->id,
            'price_usd'  => $data['usd'],
            'price_mxn'  => $data['mxn'],
            'source'     => 'CoinGecko',
            'fetched_at' => now(),
        ]);
    }

    private function fetchMetal(Asset $asset, string $symbol): void
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::timeout(60)
            ->retry(3, 2000)
            ->withHeaders([
                'x-access-token' => config('services.goldapi.key'),
            ])->get("https://www.goldapi.io/api/{$symbol}/USD");

        if (!$response->ok()) {
            throw new \Exception("GoldAPI no respondió para {$symbol}");
        }

        $usdPerOzt = $response->json()['price'];
        $mxnRate = $this->usdToMxn();

        // 🧠 Regla de unidades
        if ($symbol === 'XAU') {
            // Oro → gramos
            $priceUsd = $usdPerOzt / 31.1035;
            $unitNote = 'USD por gramo';
        } else {
            // Plata → onza troy
            $priceUsd = $usdPerOzt;
            $unitNote = 'USD por ozt';
        }

        Price::create([
            'asset_id'   => $asset->id,
            'price_usd'  => $priceUsd,
            'price_mxn'  => $priceUsd * $mxnRate,
            'source'     => "GoldAPI + Banxico ({$unitNote})",
            'fetched_at' => now(),
        ]);
    }

    private function fetchUsd(Asset $asset): void
{
    $mxnRate = $this->usdToMxn();

    Price::create([
        'asset_id'   => $asset->id,
        'price_usd'  => 1.0,      
        'price_mxn'  => $mxnRate, 
        'source'     => 'Banxico',
        'fetched_at' => now(),
    ]);
}
}