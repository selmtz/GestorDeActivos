@extends('layouts.app')

@section('content')
    <div class="header">
        <h1>Vender Activo</h1>
    </div>

    <div class="container">
        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        @if($inventories->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <div class="empty-state-text">No tienes activos para vender</div>
                <div class="empty-state-subtext">Primero compra activos para tu inventario</div>
            </div>
        @else
            <div class="form-card">
                <form method="POST" action="{{ route('sell.store') }}" id="sellForm">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Activo a vender</label>
                        <select name="asset_id" class="form-select" required id="assetSelect">
                            <option value="">Selecciona un activo</option>
                            @foreach($inventories as $inventory)
                                <option 
                                    value="{{ $inventory->asset_id }}"
                                    data-quantity="{{ $inventory->quantity }}"
                                    data-symbol="{{ $inventory->asset->symbol }}"
                                    data-unit="{{ $inventory->asset->base_unit }}"
                                    data-decimals="{{ $inventory->asset->decimals }}"
                                >
                                    {{ App\Helpers\AssetHelper::getIcon($inventory->asset->symbol) }}
                                    {{ App\Helpers\AssetHelper::getName($inventory->asset) }}
                                    ({{ App\Helpers\AssetHelper::formatQuantity($inventory->quantity, $inventory->asset) }} 
                                    {{ App\Helpers\AssetHelper::getUnit($inventory->asset) }} disponible)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unidad de venta</label>
                        <div class="unit-selector" id="unitSelector" style="display: none;">
                            <!-- Se llena dinámicamente -->
                        </div>
                        <input type="hidden" name="unit" id="unitInput" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Cantidad
                            <span id="availableQuantity" style="color: #6b7280; font-weight: 400;"></span>
                        </label>
                        <input
                            type="number"
                            name="quantity"
                            class="form-input"
                            step="0.00000001"
                            min="0"
                            placeholder="0.00"
                            required
                            id="quantityInput"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Precio unitario (USD)</label>
                        <input
                            type="number"
                            name="price_usd"
                            class="form-input"
                            step="0.01"
                            min="0.01"
                            placeholder="0.00"
                            required
                            id="priceInput"
                        >
                    </div>

                    <div id="salePreview" style="display: none; background: #f9fafb; border-radius: 12px; padding: 20px; margin-top: 20px; border: 2px solid #e5e7eb;">
                        <div style="text-align: center; margin-bottom: 16px;">
                            <div style="font-size: 14px; color: #6b7280; margin-bottom: 8px; font-weight: 600;">TOTAL DE LA VENTA</div>
                            <div style="font-size: 32px; font-weight: 700; color: #059669; margin-bottom: 4px;" id="totalUsd">
                                $0.00 USD
                            </div>
                        </div>
                        
                        <div style="border-top: 1px solid #e5e7eb; padding-top: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 14px;">
                            <div>
                                <div style="color: #9ca3af; margin-bottom: 4px;">Precio / unidad</div>
                                <div style="font-weight: 600; color: #374151;" id="unitPrice">$0.00 USD</div>
                            </div>
                            <div>
                                <div style="color: #9ca3af; margin-bottom: 4px;">Cantidad</div>
                                <div style="font-weight: 600; color: #374151;" id="quantityDisplay">0</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 20px;">
                        💰 Registrar Venta
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div class="action-buttons">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary" style="width: 100%;">
            <span>←</span>
            Volver
        </a>
    </div>

    <script>
        const assetSelect = document.getElementById('assetSelect');
        const unitSelector = document.getElementById('unitSelector');
        const unitInput = document.getElementById('unitInput');
        const quantityInput = document.getElementById('quantityInput');
        const priceInput = document.getElementById('priceInput');
        const availableQuantity = document.getElementById('availableQuantity');
        const salePreview = document.getElementById('salePreview');
        const totalUsd = document.getElementById('totalUsd');
        const unitPrice = document.getElementById('unitPrice');
        const quantityDisplay = document.getElementById('quantityDisplay');

        let maxQuantity = 0;
        let currentUnit = '';

        assetSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const symbol = option.dataset.symbol;
            const baseUnit = option.dataset.unit;
            maxQuantity = parseFloat(option.dataset.quantity) || 0;

            unitSelector.innerHTML = '';
            unitSelector.style.display = 'none';
            unitInput.value = '';

            if (!symbol) return;

            if (symbol === 'XAG') {
                // Plata: gramos u onzas troy
                unitSelector.innerHTML = `
                    <label class="unit-option">
                        <input type="radio" name="unit_radio" value="g">
                        <div>Gramos (g)</div>
                    </label>
                    <label class="unit-option">
                        <input type="radio" name="unit_radio" value="oz t">
                        <div>Onzas troy (oz t)</div>
                    </label>
                `;
                unitSelector.style.display = 'flex';
            } else {
                unitInput.value = baseUnit;
                currentUnit = baseUnit;
                availableQuantity.textContent = `(Máximo: ${maxQuantity.toFixed(4)} ${baseUnit})`;
            }

            document.querySelectorAll('input[name="unit_radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    unitInput.value = this.value;
                    currentUnit = this.value;
                    document.querySelectorAll('.unit-option').forEach(opt => opt.classList.remove('active'));
                    this.closest('.unit-option').classList.add('active');
                    availableQuantity.textContent = `(Máximo: ${maxQuantity.toFixed(4)} oz t)`;
                });
            });

            updatePreview();
        });

        quantityInput.addEventListener('input', updatePreview);
        priceInput.addEventListener('input', updatePreview);

        function updatePreview() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            
            if (quantity > 0 && price > 0) {
                salePreview.style.display = 'block';
                
                const total = quantity * price;
                
                totalUsd.textContent = `$${total.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} USD`;
                unitPrice.textContent = `$${price.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} USD`;
                quantityDisplay.textContent = `${quantity.toFixed(4)} ${currentUnit}`;
            } else {
                salePreview.style.display = 'none';
            }
        }

        document.getElementById('sellForm').addEventListener('submit', function(e) {
            if (!unitInput.value) {
                e.preventDefault();
                alert('Por favor selecciona una unidad');
            }
        });
    </script>
@endsection