<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

return new class extends Migration
{
    public function up(): void
    {
       Asset::insert([
            // Metales
            ['name' => 'Oro', 'symbol' => 'XAU', 'unit' => 'g', 'active' => true],
            ['name' => 'Plata', 'symbol' => 'XAG', 'unit' => 'ozt', 'active' => true],

            // Dineros
            ['name' => 'Dólar estadounidense', 'symbol' => 'USD', 'unit' => 'usd', 'active' => true],

            // Criptos (todas = coin)
            ['name' => 'Bitcoin', 'symbol' => 'BTC', 'unit' => 'coin', 'active' => true],
            ['name' => 'Ethereum', 'symbol' => 'ETH', 'unit' => 'coin', 'active' => true],
            ['name' => 'Solana', 'symbol' => 'SOL', 'unit' => 'coin', 'active' => true],
            ['name' => 'Litecoin', 'symbol' => 'LTC', 'unit' => 'coin', 'active' => true],
        ]);

    }
}

