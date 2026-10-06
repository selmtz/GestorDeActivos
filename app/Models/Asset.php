<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
    'name',
    'symbol',
    'unit',
    'active'
    ];

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function transactions()
    {
    return $this->hasMany(Transaction::class);
    }

}
