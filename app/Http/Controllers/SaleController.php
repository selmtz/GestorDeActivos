<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Cashflow;
use App\Helpers\AssetHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function create()
    {
        $inventories = Inventory::with('asset')
            ->where('quantity', '>', 0)
            ->get();

        return view('sales.create', compact('inventories'));
    }

    public function sell(Request $request)
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'quantity' => 'required|numeric|min:0.00000001',
            'unit' => 'required|in:g,oz t,BTC,ETH,SOL,LTC',
            'price_usd' => 'required|numeric|min:0.01',
        ]);

        $asset = Asset::findOrFail($data['asset_id']);
        
        $quantityInBaseUnit = AssetHelper::convertToBaseUnit(
            $data['quantity'],
            $data['unit'],
            $asset
        );

        try {
            DB::transaction(function () use ($data, $asset, $quantityInBaseUnit) {
                $inventory = Inventory::where('asset_id', $data['asset_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || $inventory->quantity < $quantityInBaseUnit) {
                    throw new \Exception('Inventario insuficiente');
                }

                $totalUsd = $quantityInBaseUnit * $data['price_usd'];

                Cashflow::create([
                    'type' => 'sell',
                    'asset_id' => $asset->id,
                    'quantity' => $quantityInBaseUnit,
                    'price_usd' => $data['price_usd'],
                    'total_usd' => $totalUsd,
                    'notes' => "Venta de {$quantityInBaseUnit} {$asset->base_unit}",
                ]);

                // Descontar del inventario
                $inventory->decrement('quantity', $quantityInBaseUnit);
            });

            return redirect()->route('inventory.index')->with('success', 'Venta registrada exitosamente');
            
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}