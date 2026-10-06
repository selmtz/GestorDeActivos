<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'asset_id',
        'price_usd',
        'price_mxn',
        'source',
        'fetched_at',
    ];
    
    //convertir fetched_at a Carbon
    protected $casts = [
        'fetched_at' => 'datetime',
    ];
    
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

}
