<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Price;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FetchCryptoPrices extends Command
{
    protected $signature = 'prices:fetch-crypto';
    protected $description = 'Obtener precios de todas las criptomonedas desde CoinGecko';

    public function handle()
    {
        // Obtener todas las criptomonedas activas (unit = 'btc')
        $cryptoAssets = Asset::where('unit', 'coin')
            ->where('active', true)
            ->get();

        if ($cryptoAssets->isEmpty()) {
            $this->error('No hay criptomonedas configuradas');
            return 1;
        }

        // Mapear símbolos a IDs de CoinGecko
        $mapping = config('crypto.coingecko_mapping');
        $coingeckoIds = [];
        $assetsByCoingeckoId = [];

        foreach ($cryptoAssets as $asset) {
            if (isset($mapping[$asset->symbol])) {
                $coingeckoId = $mapping[$asset->symbol];
                $coingeckoIds[] = $coingeckoId;
                $assetsByCoingeckoId[$coingeckoId] = $asset;
            }
        }

        if (empty($coingeckoIds)) {
            $this->error('No hay criptomonedas mapeadas');
            return 1;
        }

        try {
            // UNA SOLA LLAMADA para todas las criptos
            $response = Http::timeout(60)   //si coingecko no responde se corta la conexion
                ->retry(3, 2000)           //si coingecko no responde se reintenta 3 veces  cada 2 segundos
                ->get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => implode(',', $coingeckoIds),
                    'vs_currencies' => 'usd,mxn',
                ]);

            if (!$response->ok()) {
                Log::warning('CoinGecko no respondió', [
                    'status' => $response->status()
                ]);
                $this->error('CoinGecko no respondió. Se mantienen precios mas recientes');
                return 1;
            }

            $data = $response->json();

            // Guardar precios de cada cripto
            foreach ($data as $coingeckoId => $prices) {
                if (!isset($assetsByCoingeckoId[$coingeckoId])) {
                    continue;
                }

                if (!isset($prices['usd']) || !isset($prices['mxn'])) {
                    $this->warn("Datos incompletos para {$coingeckoId}");
                    continue;
                }

                if ($prices['usd'] <= 0 || $prices['mxn'] <= 0) {
                    $this->warn('Precio invalido para {$coingeckoId} (<=0)');
                    continue;
                }
                $asset = $assetsByCoingeckoId[$coingeckoId];

                Price::create([
                    'asset_id'   => $asset->id,
                    'price_usd'  => $prices['usd'],
                    'price_mxn'  => $prices['mxn'],
                    'source'     => 'CoinGecko',
                    'fetched_at' => now(),
                ]);

                $this->info("✓ {$asset->symbol} actualizado");
            }

            $this->info('✓ Todas las criptomonedas actualizadas');
            return 0;
            
        } catch (\Exception $e) {
            Log::error('Error en FetchCryptoPrices', [
                'error' => $e->getMessage()
            ]);
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
