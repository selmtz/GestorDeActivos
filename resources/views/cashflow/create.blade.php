@extends('layouts.app')

@section('content')
    <div class="header">
        <h1>Gestionar Fondos</h1>
    </div>

    <div class="container">
        <div class="balance-display">
            <div class="balance-label">Balance Actual</div>
            <div class="balance-amount">${{ number_format($balance, 2) }} USD</div>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="form-card">
            <form method="POST" action="{{ route('cashflow.store') }}" id="cashflowForm">
                @csrf

                <div class="type-selector">
                    <label class="type-option" id="depositOption">
                        <input type="radio" name="type" value="deposit" required>
                        <div class="type-icon">➕</div>
                        <div class="type-label">Depositar</div>
                    </label>
                    <label class="type-option" id="withdrawOption">
                        <input type="radio" name="type" value="withdraw" required>
                        <div class="type-icon">➖</div>
                        <div class="type-label">Retirar</div>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Monto (USD)</label>
                    <input
                        type="number"
                        name="amount"
                        class="form-input"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        required
                        id="amountInput"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Notas (opcional)</label>
                    <textarea
                        name="notes"
                        class="form-input"
                        rows="3"
                        placeholder="Ej: Depósito inicial, retiro para gastos, etc."
                        style="resize: vertical;"
                    ></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    Confirmar
                </button>
            </form>
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ route('cashflow.index') }}" class="btn btn-secondary" style="width: 100%;">
            <span>←</span>
            Volver
        </a>
    </div>

    <script>
        const depositOption = document.getElementById('depositOption');
        const withdrawOption = document.getElementById('withdrawOption');
        const amountInput = document.getElementById('amountInput');
        const balance = {{ $balance }};

        document.querySelectorAll('input[name="type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
                this.closest('.type-option').classList.add('active');
            });
        });

        document.getElementById('cashflowForm').addEventListener('submit', function(e) {
            const type = document.querySelector('input[name="type"]:checked');
            const amount = parseFloat(amountInput.value) || 0;

            if (!type) {
                e.preventDefault();
                alert('Selecciona el tipo de operación');
                return;
            }

            if (type.value === 'withdraw' && amount > balance) {
                e.preventDefault();
                alert(`No puedes retirar más de tu balance actual ($${balance.toFixed(2)} USD)`);
            }
        });
    </script>
@endsection