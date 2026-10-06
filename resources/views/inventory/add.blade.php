@extends('layouts.app')

@section('content')
    <!---------------------HEADER---------------------->
    <div class="header">
        <h1>Agregar activo</h1>
    </div>

    <div class="container">
        <div class="form-card">
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Activo</label>
                    <select name="asset_id" class="form-select" required>
                        
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ App\Helpers\AssetHelper::getName($asset['symbol']) }}
                            </option>
                        @endforeach
                    </select>
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
                >
                </div>    

                <button type="submit" class="btn-submit"> 
                    Agregar al inventario
                </button>
            </form>
        </div>
    </div>

    <!-- BOTONES DE ACCIÓN FIJOS -->
    <div class="action-buttons">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary" style="width: 100%;">
            <span>←</span>
            Volver
        </a>
        <a href="{{ route('sell.create') }}" class="btn btn-secondary">
            <span>💰</span>
            Vender
        </a>
    </div>
@endsection
