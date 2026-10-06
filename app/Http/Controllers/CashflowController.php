<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Cashflow;
use Illuminate\Http\Request;

class CashflowController extends Controller
{
    public function index(Request $request) 
    {
        Carbon::setLocale('es');

        $query = Cashflow::with('asset')->orderBy('created_at', 'desc');

        //filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();
        $balance = Cashflow::getBalance();
        $assets = Asset::where('active', true)->get();

        return view('cashflow.index', compact('transactions', 'balance', 'assets'));
    }

    public function create(Request $request)
    {
        $balance = Cashflow::getBalance();
        return view('cashflow.create', compact('balance'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:deposit, withdraw',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        //si es retiro, verificar fondos
        if ($data['type'] === 'withdraw') {
            $amount = $data['amount'];

            if (!Cashflow::hasSufficientFunds($amount)) {
                return back()->with('error', 'Fondos insuficientes');
            }

            $totalUsd = -$amount;  //negativo para retirar
        } else {
            $totalUsd = $data['amount']; //positivo para depositar
        }

        Cashflow::create([
            'type' => $data['type'],
            'total_usd' => $totalUsd, 
            'notes' => $data['notes'] ?? null, 
        ]);

        $message = $data['type'] === 'deposit' ? 'Fondos agregados' : 'Retiro registrado';

        return redirect()->route('cashflow.index')->with('success', $message);
    }
}