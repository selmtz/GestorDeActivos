<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FetchPrices extends Command
{
    protected $signature = 'prices:fetch';
    protected $description = 'Obtener TODOS los precios (manual)';

    public function handle()
    {
        $this->info('Obteniendo precios de criptomonedas...');
        Artisan::call('prices:fetch-crypto');
        
        $this->info('Obteniendo precios de metales...');
        Artisan::call('prices:fetch-metals');

        $this->info('Actualizando Dólar...');
        Artisan::call('prices:fetch-fiat');
        
        $this->info('✓ Todos los precios actualizados');
    }
}
