@extends('layouts.app')

@section('content')
    <div class="header">
        <h1>Historial</h1>
        <div style="font-size: 14px; opacity: 0.85; margin-top: 8px;">
            Todas tus transacciones
        </div>
    </div>

    <div class="container">
        <div class="section">
            <div class="section-title">Compras y Ventas</div>

            @forelse($transactions as $transaction)
                <div class="asset-card">
                    <div class="asset-header">
                        <div class="asset-icon {{ App\Helpers\AssetHelper::getColorClass($transaction->asset->symbol) }}">
                            {{ App\Helpers\AssetHelper::getIcon($transaction->asset->symbol) }}
                        </div>
                        <div class="asset-info">
                            <div class="asset-name">
                                {{ $transaction->type === 'sell' ? 'Venta' : 'Compra' }} de 
                                {{ App\Helpers\AssetHelper::getName($transaction->asset) }}
                            </div>
                            <div class="asset-quantity">
                                {{ $transaction->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    <div class="asset-details">
                        <div class="detail-item">
                            <div class="detail-label">Cantidad</div>
                            <div class="detail-value">
                                {{ App\Helpers\AssetHelper::formatQuantity($transaction->quantity, $transaction->asset) }}
                                {{ App\Helpers\AssetHelper::getUnit($transaction->asset) }}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total</div>
                            <div class="detail-value large" style="color: {{ $transaction->type === 'sell' ? '#059669' : '#dc2626' }};">
                                {{ $transaction->type === 'sell' ? '+' : '-' }}${{ number_format(abs($transaction->total_usd), 2) }} USD
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6; font-size: 13px; color: #6b7280;">
                        📅 {{ $transaction->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-state-icon">📜</div>
                    <div class="empty-state-text">Sin transacciones</div>
                    <div class="empty-state-subtext">Tus compras y ventas aparecerán aquí</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection