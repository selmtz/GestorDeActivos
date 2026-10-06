<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'asset_id',
        'quantity',
        'price_usd',
        'price_mxn',
        'total_usd',
        'total_mxn',
        'type'
    ];

     public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
