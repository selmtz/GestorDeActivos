@extends('layouts.app')

@section('content')
    <div class="header">
        <h1>Comprar Activo</h1>
    </div>

    <div class="container">
        <div class="balance-display">
            <div class="balance-label">Balance Disponible</div>
            <div class="balance-amount">${{ number_format($balance, 2) }} USD</div>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="form-card">
            <form method="POST" action="{{ route('buy.store') }}" id="buyForm">
                @csrf

                    <div class="form-group">
                    <label class="form-label">Activo</label>
                    <select name="asset_id" class="form-select" required id="assetSelect">
                        <option value="">Selecciona un activo</option>
                        @foreach($assets as $asset)
                            <option 
                                value="{{ $asset->id }}"
                                data-symbol="{{ $asset->symbol }}"
                                data-unit="{{ $asset->base_unit }}"
                                data-decimals="{{ $asset->decimals }}"
                            >
                                {{ $asset->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Unidad de compra</label>
                    <div class="unit-selector" id="unitSelector" style="display: none;">
                        <!-- Se llena dinámicamente -->
                    </div>
                    <input type="hidden" name="unit" id="unitInput" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Cantidad</label>
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

                <div id="totalPreview" style="display: none; background: #f9fafb; border-radius: 12px; padding: 16px; margin-top: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 14px; color: #6b7280; font-weight: 600;">Total:</span>
                        <span style="font-size: 24px; font-weight: 700; color: #dc2626;" id="totalAmount">$0.00 USD</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="margin-top: 20px;">
                    🛒 Comprar
                </button>
            </form>
        </div>
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
        const totalPreview = document.getElementById('totalPreview');
        const totalAmount = document.getElementById('totalAmount');

        assetSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const symbol = option.dataset.symbol;
            const baseUnit = option.dataset.unit;

            unitSelector.innerHTML = '';
            unitSelector.style.display = 'none';
            unitInput.value = '';

            if (!symbol) return;

            // Mostrar opciones de unidad según el activo
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
                // Otros activos: usar su unidad base
                unitInput.value = baseUnit;
            }

            // Configurar listeners para las opciones de unidad
            document.querySelectorAll('input[name="unit_radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    unitInput.value = this.value;
                    document.querySelectorAll('.unit-option').forEach(opt => opt.classList.remove('active'));
                    this.closest('.unit-option').classList.add('active');
                });
            });

            updateTotal();
        });

        quantityInput.addEventListener('input', updateTotal);
        priceInput.addEventListener('input', updateTotal);

        function updateTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;

            if (quantity > 0 && price > 0) {
                const total = quantity * price;
                totalAmount.textContent = '$' + total.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' USD';
                totalPreview.style.display = 'block';
            } else {
                totalPreview.style.display = 'none';
            }
        }

        document.getElementById('buyForm').addEventListener('submit', function(e) {
            const balance = {{ $balance }};
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;

            if (total > balance) {
                e.preventDefault();
                alert(`Fondos insuficientes. Balance: $${balance.toFixed(2)} USD`);
            }

            if (!unitInput.value) {
                e.preventDefault();
                alert('Por favor selecciona una unidad');
            }
        });
    </script>
@endsection