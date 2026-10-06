<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Cashflow;
use App\Models\Inventory;
use App\Models\Price;
use App\Helpers\AssetHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function create()
    {
        $assets = Asset::where('active', true)->get();
        $balance = Cashflow::getBalance();

        return view('purchases.create', compact('assets', 'balance'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'quantity' => 'required|numeric|min:0.00000001',
            'unit' => 'required|in:g,oz t,BTC,ETH,SOL,LTC',
            'price_usd' => 'required|numeric|min:0.01',
        ]);

        $asset = Asset::findOrFail($data['asset_id']);
        
        // Convertir cantidad a unidad base si es necesario
        $quantityInBaseUnit = AssetHelper::convertToBaseUnit(
            $data['quantity'],
            $data['unit'],
            $asset
        );

        // Calcular total
        $totalUsd = $quantityInBaseUnit * $data['price_usd'];

        // Verificar fondos suficientes
        if (!Cashflow::hasSufficientFunds($totalUsd)) {
            return back()->with('error', 'Fondos insuficientes. Balance actual: $' . number_format(Cashflow::getBalance(), 2) . ' USD');
        }

        try {
            DB::transaction(function () use ($data, $asset, $quantityInBaseUnit, $totalUsd) {
                // Registrar compra en cashflow (negativo)
                Cashflow::create([
                    'type' => 'buy',
                    'asset_id' => $asset->id,
                    'quantity' => $quantityInBaseUnit,
                    'price_usd' => $data['price_usd'],
                    'total_usd' => -$totalUsd,
                    'notes' => "Compra de {$quantityInBaseUnit} {$asset->base_unit}",
                ]);

                // Actualizar inventario
                $inventory = Inventory::firstOrCreate(
                    ['asset_id' => $asset->id],
                    ['quantity' => 0]
                );

                $inventory->quantity += $quantityInBaseUnit;
                $inventory->save();
            });

            return redirect()->route('inventory.index')->with('success', 'Compra registrada exitosamente');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }
}