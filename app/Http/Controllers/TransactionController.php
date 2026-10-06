<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');
        
        // Solo mostrar compras y ventas (no depósitos ni retiros)
        $transactions = Cashflow::with('asset')
            ->whereIn('type', ['buy', 'sell'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transactions.index', compact('transactions'));
    }
}
