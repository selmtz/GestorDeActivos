<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\Cashflow;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario solo si no existe
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Limpiar datos anteriores
        Asset::query()->delete();
        Cashflow::query()->delete();

        // Crear activos con sus unidades y decimales
        $assets = [
            ['name' => 'Bitcoin', 'symbol' => 'BTC', 'base_unit' => 'BTC', 'decimals' => 8, 'active' => true],
            ['name' => 'Ethereum', 'symbol' => 'ETH', 'base_unit' => 'ETH', 'decimals' => 8, 'active' => true],
            ['name' => 'Solana', 'symbol' => 'SOL', 'base_unit' => 'SOL', 'decimals' => 8, 'active' => true],
            ['name' => 'Litecoin', 'symbol' => 'LTC', 'base_unit' => 'LTC', 'decimals' => 8, 'active' => true],
            ['name' => 'Oro', 'symbol' => 'XAU', 'base_unit' => 'g', 'decimals' => 4, 'active' => true],
            ['name' => 'Plata', 'symbol' => 'XAG', 'base_unit' => 'oz t', 'decimals' => 4, 'active' => true],
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }

        // Agregar un balance inicial de cashflow
        Cashflow::create([
            'type' => 'deposit',
            'total_usd' => 10000.00,
            'notes' => 'Balance inicial',
        ]);

        $this->command->info('✓ Assets y cashflow inicial creados');
    }
}