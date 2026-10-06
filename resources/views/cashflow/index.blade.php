@extends('layouts.app')

@section('content')
    <div class="header">
        <h1>Cashflow</h1>
        <div class="total-usd">${{ number_format($balance, 2) }} USD</div>
        <div style="font-size: 14px; opacity: 0.85; margin-top: 8px;">Balance disponible</div>
    </div>

    <div class="container">
        <!-- FILTROS -->
        <div class="filters">
            <form method="GET" action="{{ route('cashflow.index') }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Tipo</label>
                        <select name="type" class="filter-select">
                            <option value="">Todos</option>
                            <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Depósito</option>
                            <option value="withdraw" {{ request('type') === 'withdraw' ? 'selected' : '' }}>Retiro</option>
                            <option value="buy" {{ request('type') === 'buy' ? 'selected' : '' }}>Compra</option>
                            <option value="sell" {{ request('type') === 'sell' ? 'selected' : '' }}>Venta</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Activo</label>
                        <select name="asset_id" class="filter-select">
                            <option value="">Todos</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                    {{ App\Helpers\AssetHelper::getName($asset) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Desde</label>
                        <input type="date" name="date_from" class="filter-input" value="{{ request('date_from') }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Hasta</label>
                        <input type="date" name="date_to" class="filter-input" value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter btn-apply">Filtrar</button>
                    <a href="{{ route('cashflow.index') }}" class="btn-filter btn-clear">Limpiar</a>
                </div>
            </form>
        </div>

        <!-- TRANSACCIONES -->
        <div class="section">
            <div class="section-title">Transacciones</div>

            @forelse($transactions as $transaction)
                <div class="asset-card">
                    <div class="asset-header">
                        @if($transaction->asset)
                            <div class="asset-icon {{ App\Helpers\AssetHelper::getColorClass($transaction->asset->symbol) }}">
                                {{ App\Helpers\AssetHelper::getIcon($transaction->asset->symbol) }}
                            </div>
                        @else
                            <div class="asset-icon bitcoin">
                                💵
                            </div>
                        @endif
                        
                        <div class="asset-info">
                            <div class="asset-name">
                                @if($transaction->type === 'deposit')
                                    Depósito de fondos
                                @elseif($transaction->type === 'withdraw')
                                    Retiro de fondos
                                @elseif($transaction->type === 'buy')
                                    Compra de {{ $transaction->asset ? App\Helpers\AssetHelper::getName($transaction->asset) : 'activo' }}
                                @else
                                    Venta de {{ $transaction->asset ? App\Helpers\AssetHelper::getName($transaction->asset) : 'activo' }}
                                @endif
                                
                                <span class="transaction-type type-{{ $transaction->type }}">
                                    {{ strtoupper($transaction->type) }}
                                </span>
                            </div>
                            <div class="asset-quantity">
                                {{ $transaction->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    
                    @if($transaction->asset && $transaction->quantity)
                        <div class="asset-details">
                            <div class="detail-item">
                                <div class="detail-label">Cantidad</div>
                                <div class="detail-value">
                                    {{ App\Helpers\AssetHelper::formatQuantity($transaction->quantity, $transaction->asset) }}
                                    {{ App\Helpers\AssetHelper::getUnit($transaction->asset) }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Precio unitario</div>
                                <div class="detail-value">
                                    ${{ number_format($transaction->price_usd, 2) }} USD
                                </div>
                            </div>
                        </div>
                    @endif

                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #6b7280;">Total:</span>
                            <span style="font-size: 20px; font-weight: 700; color: {{ $transaction->total_usd >= 0 ? '#059669' : '#dc2626' }};">
                                {{ $transaction->total_usd >= 0 ? '+' : '' }}${{ number_format(abs($transaction->total_usd), 2) }} USD
                            </span>
                        </div>
                        @if($transaction->notes)
                            <div style="margin-top: 8px; font-size: 13px; color: #6b7280;">
                                {{ $transaction->notes }}
                            </div>
                        @endif
                        <div style="margin-top: 8px; font-size: 13px; color: #9ca3af;">
                            📅 {{ $transaction->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-text">Sin transacciones</div>
                    <div class="empty-state-subtext">Tus movimientos aparecerán aquí</div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ route('cashflow.create') }}" class="btn btn-primary" style="width: 100%;">
            <span>💵</span>
            Agregar / Retirar
        </a>
    </div>
@endsection