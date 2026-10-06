<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'asset_id',
        'quantity',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
