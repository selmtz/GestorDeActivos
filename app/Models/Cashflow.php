<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    protected $table = 'cashflow';
    
    protected $fillable = [
        'type',
        'asset_id',
        'quantity',
        'price_usd',
        'total_usd',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Obtener balance total de cashflow
     */
    public static function getBalance(): float
    {
        return (float) self::sum('total_usd');
    }

    /**
     * Verificar si hay fondos suficientes
     */
    public static function hasSufficientFunds(float $amount): bool
    {
        return self::getBalance() >= $amount;
    }
}
