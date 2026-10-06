<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Price;
use App\Models\Inventory;
use App\Models\Cashflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    public function index()
    {
        $data = $this->getInventoryData();
        return view('inventory.index', $data);
    }

    public function data()
    {
        $data = $this->getInventoryData();

        // CORREGIDO: Había un error de sintaxis aquí json($data)([...])
        return response()->json([
            'assets' => $data['assets'],
            'totalUsd' => $data['totalUsd'],
            'totalMxn' => $data['totalMxn'],
            'cashflowBalance' => $data['cashflowBalance'],
            'lastUpdate' => $data['lastUpdate'] ? $data['lastUpdate']->diffForHumans() : 'Sin precios disponibles'
        ]);
    }

    public function create()
    {
        $assets = Asset::where('active', true)->get();
        return view('inventory.add', compact('assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'quantity' => 'required|numeric|min:0.00000001',
        ]);

        $inventory = Inventory::firstOrCreate(
            ['asset_id' => $data['asset_id']],
            ['quantity' => 0]
        );

        $inventory->quantity += $data['quantity'];
        $inventory->save();

        return redirect()->route('inventory.index')->with('success', 'Inventario actualizado exitosamente');
    }

    // Metodo que contiene la logica compartida
    private function getInventoryData(): array
    {
        Carbon::setLocale('es');
        $inventory = Inventory::with('asset')->get();

        $usdAsset = Asset::where('symbol', 'USD')->first();

        $latestUsdPrice = $usdAsset 
            ? Price::where('asset_id', $usdAsset->id)->latest('fetched_at')->first() 
            : null;

        $usdToMxn = $latestUsdPrice ? $latestUsdPrice->price_mxn : 20.0;


        $assets = [];
        $totalUsd = 0;
        $totalMxn = 0;
        $lastUpdate = null;

        foreach ($inventory as $item) {
            $prices = Price::where('asset_id', $item->asset_id)
                ->orderBy('fetched_at', 'desc')
                ->take(2)
                ->get();

            if ($prices->isEmpty()) {
                $priceUsd = 0;
                $priceMxn = 0;
                $trend = 'neutral';
                $diffAbsolute = 0;
                $diffPercentage = 0;
                $hasPrice = false;
            } else {
                $currentPrice = $prices->first();
                $priceUsd = $currentPrice->price_usd;
                $priceMxn = $priceUsd * $usdToMxn;
                $hasPrice = true;

                if (!$lastUpdate || $currentPrice->fetched_at > $lastUpdate) {
                    $lastUpdate = $currentPrice->fetched_at;
                }

                // Calcular variacion
                if ($prices->count() >= 2) {
                    $previousPrice = $prices->last();
                    $diffAbsolute = $currentPrice->price_usd - $previousPrice->price_usd;

                    // Diferencia en porcentaje
                    if ($previousPrice->price_usd > 0) {
                        $diffPercentage = $diffAbsolute / $previousPrice->price_usd * 100;
                    } else {
                        $diffPercentage = 0;
                    }
                    
                    // Calcular tendencia
                    if ($diffAbsolute > 0) {
                        $trend = 'up';
                    } elseif ($diffAbsolute < 0) {
                        $trend = 'down';
                    } else {
                        $trend = 'neutral';
                        $trend = 'neutral';
                    }
                } else {
                    $trend = 'neutral';
                    $diffAbsolute = 0;
                    $diffPercentage = 0;
                }
            }
            
            $valueUsd = $item->quantity * $priceUsd;
            $valueMxn = $valueUsd * $usdToMxn;
            $valueMxn = $valueUsd * $usdToMxn;

            $totalUsd += $valueUsd;
            $totalMxn += $valueMxn;

            // Convertimos la diferencia absoluta (que estaba en USD) a MXN
            // para que coincida con el precio mostrado en la vista.
            $diffMxn = $diffAbsolute * $usdToMxn;

            $assets[] = [
                'symbol'    => $item->asset->symbol, 
                'quantity'  => $item->quantity,
                'price_usd' => $priceUsd,
                'price_mxn' => $priceMxn,
                'value_usd' => $valueUsd,
                'value_mxn' => $valueMxn,
                'trend'     => $trend,
                
                'diff'            => $diffMxn, 
                'diff_absolute'   => $diffAbsolute,
                'diff_percentage' => $diffPercentage,
                'has_price'       => $hasPrice,
            ];  
        }

        return [
            'assets' => $assets,
            'totalUsd' => $totalUsd,
            'totalMxn' => $totalMxn,
            'lastUpdate' => $lastUpdate,
            'usdToMxn' => $usdToMxn,
        ];
    }
}