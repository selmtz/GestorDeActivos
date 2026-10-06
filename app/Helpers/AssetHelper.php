<?php

namespace App\Helpers;

class AssetHelper
{
    
    //formatear cantidad con decimales del activo
    public static function formatQuantity(float $quantity, Asset $asset): string
    {
       return number_format($quantity, $asset->decimals);
    }

    //obtener unidad base del activo
    public static function getUnit(Asset $asset): string
    {
        return $asset->base_unit;
    }

    //formatear precio en USD
    public static function formatPrice(float $price, int $decimals = 2): string
    {
        return number_format($price, $decimals, '.', ',');
    }

    //obtener icono del activo
    public static function getIcon(string $symbol): string
    {
        return match (strtolower($symbol)) {
            'xau' => '🟡',
            'xag' => '⚪',
            'btc' => '₿',
            'eth' => '💎',
            'sol' => '◎',
            'ltc' => 'Ł',
            default => '🪙',
        };
    }

    //obtener nombre del activo
    public static function getName(string $symbol): string
    {
        return match (strtolower($symbol)) {
            'xau' => 'Oro',
            'xag' => 'Plata',
            'btc' => 'Bitcoin',
            'eth' => 'Ethereum',
            'sol' => 'Solana',
            'ltc' => 'Litecoin',
            default => $symbol,
        };
    }
    //obtener clase de color para el activo
    public static function getColorClass(string $symbol): string
    {
        return match (strtolower($symbol)) {
            'xau' => 'gold',
            'xag' => 'silver',
            'btc' => 'bitcoin',
            'eth' => 'ethereum',
            'sol' => 'solana',
            'ltc' => 'litecoin',
            default => 'bitcoin',
        };
    }

    //convertir gramos a onzas troy
    public static function gramsToOunces(float $grams): float
    {
        return $grams / 31.1034768;
    }

    //convertir onzas troy a gramos
    public static function ouncesToGrams(float $ounces): float
    {
        $symbol = strtolower($symbol);

        // Regla 1: Plata (XAG) es onza
        if ($symbol === 'xag') {
            return 'oz'; 
        }

        // Regla 2: Criptos y Divisas (USD)
        // Agregamos 'usd' a la lista. 
        // La unidad será "USD" en lugar de "g" u "oz".
        if (in_array($symbol, ['btc', 'eth', 'sol', 'ltc', 'usdc', 'usd'])) {
            return strtoupper($symbol);
        }

        // Regla 3: El resto (Oro 'xau', etc.) son gramos
        return 'g';
    }

    public static function getDecimals(string $symbol): int
{
    // Si es Dólar, Oro o Plata, usamos 2 decimales
    if (in_array(strtolower($symbol), ['usd', 'xau', 'xag'])) {
        return 2;
    }
    
    // Para todo lo demás (Criptos como BTC, ETH), usamos 8
    return 8;
}
}