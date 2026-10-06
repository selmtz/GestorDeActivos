@extends('layouts.app')

@section('content')
    <!-- HEADER -->
    <div class="header">
        <h1>Inventario</h1>
        <div class="total-mxn">${{ number_format($totalMxn, 2) }} MXN</div>
        <div class="total-usd">≈ ${{ number_format($totalUsd, 2) }} USD</div>
        <div class="cashflow-info">
            Cashflow: ${{ number_format($cashflowBalance, 2) }} USD
        </div>
        <div class="last-update">
            @if($lastUpdate)
                Actualizado {{ $lastUpdate->diffForHumans() }}
            @else
                Sin precios disponibles
            @endif
        </div>
    </div>

    <div class="container">
        <!-- MIS ACTIVOS -->
        <div class="section">
            <div class="section-title">Mis Activos</div>

            @forelse($assets as $item)
                <div class="asset-card">
                    <div class="asset-header">
                        <div class="asset-icon {{ strtolower($item['asset']->symbol) === 'xau' ? 'gold' : (strtolower($item['asset']->symbol) === 'xag' ? 'silver' : 'bitcoin') }}">
                            @if(strtolower($item['asset']->symbol) === 'xau')
                                🟡
                            @elseif(strtolower($item['asset']->symbol) === 'xag')
                                ⚪
                            @else
                                🪙
                            @endif
                        </div>
                        
                        <div class="asset-info">
                            <div class="asset-name">
                                {{ $item['asset']->name }}
                            </div>
                            <div class="asset-quantity">
                                {{ number_format($asset['quantity'], App\Helpers\AssetHelper::getDecimals($asset['symbol'])) }} 
                                {{ App\Helpers\AssetHelper::getUnit($asset['symbol']) }}
                            </div>
                        </div>
                    </div>

                    <div class="asset-details">
                        
                        <div class="detail-item">
                            <div class="detail-label">
                                Precio MXN
                            </div>

                            @if($asset['has_price'])
                                <div class="detail-value">
                                    ${{ number_format($asset['price_mxn'], 2) }}
                                </div>
                                
                                {{-- TENDENCIA (Calculada en Pesos) --}}
                                @if($asset['diff_percentage'] !== null)
                                    @if($asset['trend'] === 'up')
                                        <div class="price-change" style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: #059669; font-weight: 600;">
                                            <span>▲</span>
                                            {{-- Usamos 'diff' que ya viene en MXN desde el controlador --}}
                                            +${{ number_format(abs($asset['diff']), 2) }}
                                            ({{ number_format($asset['diff_percentage'], 2) }}%)
                                        </div>
                                    @elseif($asset['trend'] === 'down')
                                        <div class="price-change" style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: #dc2626; font-weight: 600;">
                                            <span>▼</span>
                                            -${{ number_format(abs($asset['diff']), 2) }}
                                            ({{ number_format(abs($asset['diff_percentage']), 2) }}%)
                                        </div>
                                    @else
                                        <div class="price-change" style="font-size: 13px; color: #9ca3af;">
                                            <span>━</span> Sin cambios
                                        </div>
                                    @endif
                                @else
                                    <div class="price-change" style="font-size: 13px; color: #9ca3af;">
                                        <span>━</span> Datos insuf.
                                    </div>
                                @endif

                            @else
                                <div class="detail-value" style="color: #9ca3af;">Sin precio</div>
                            @endif
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Valor Total</div>
                            
                            {{-- MXN en GRANDE --}}
                            <div class="detail-value large">
                                ${{ number_format($asset['value_mxn'], 2) }} MXN
                            </div>
                            
                            {{-- USD en PEQUEÑO (Referencia) --}}
                            <div class="detail-subvalue" style="font-size: 13px; color: #6b7280; margin-top: 2px;">
                                ≈ ${{ number_format($asset['value_usd'], 2) }} USD
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <div class="empty-state-text">No tienes activos aún</div>
                    <div class="empty-state-subtext">Comienza comprando tu primer activo</div>
                </div>
            @endforelse
        </div>
    </div>
    
    <!--Para que Laravel ejecute comandos programados, necesitas agregar esto al crontab de tu servidor:
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1 -->
@endsection

@push('scripts')
<script>
    let isUpdating = false;

    async function updateInventory() {
        if (isUpdating) return;
        isUpdating = true;

        try {
            const response = await fetch('{{ route('inventory.data') }}');
            const data = await response.json();

            document.querySelector('.total-mxn').textContent = '$' + formatNumber(data.totalMxn, 2) + ' MXN';
            document.querySelector('.total-usd').textContent = '≈ $' + formatNumber(data.totalUsd, 2) + ' USD';
            document.querySelector('.cashflow-info').textContent = '💵 Cashflow: $' + formatNumber(data.cashflowBalance, 2) + ' USD';
            document.querySelector('.last-update').textContent = data.lastUpdate;

            data.assets.forEach((item, index) => {
                const card = document.querySelectorAll('.asset-card')[index];
                if (!card) return;

                const priceEl = card.querySelectorAll('.detail-value')[0];
                priceEl.textContent = '$' + formatNumber(item.price_usd, 2);

                const trendDiv = card.querySelector('.price-change');
                if (trendDiv) {
                    if (item.trend === 'up') {
                        trendDiv.innerHTML = '<span>▲</span> +$' + formatNumber(Math.abs(item.price_change_amount), 2) + ' (' + formatNumber(Math.abs(item.price_change_percentage), 2) + '%)';
                        trendDiv.className = 'price-change price-up';
                    } else if (item.trend === 'down') {
                        trendDiv.innerHTML = '<span>▼</span> -$' + formatNumber(Math.abs(item.price_change_amount), 2) + ' (' + formatNumber(Math.abs(item.price_change_percentage), 2) + '%)';
                        trendDiv.className = 'price-change price-down';
                    } else {
                        trendDiv.innerHTML = '<span>━</span> Sin cambios';
                        trendDiv.className = 'price-change price-neutral';
                    }
                }

                const valueTotalEl = card.querySelectorAll('.detail-value')[1];
                valueTotalEl.textContent = '$' + formatNumber(item.value_usd, 2) + ' USD';
            });

        } catch (error) {
            console.error('Error actualizando inventario:', error);
        } finally {
            isUpdating = false;
        }
    }

    function formatNumber(num, decimals) {
        return parseFloat(num).toLocaleString('es-MX', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    setInterval(updateInventory, 60000);
</script>
@endpush




