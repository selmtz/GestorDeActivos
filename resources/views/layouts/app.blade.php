<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MetalValue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f5f5f7;
            min-height: 100vh;
            padding-bottom: 80px;
        }
        /*HEADER DEL VALOR TOTAL*/
        .header {
            background: linear-gradient(135deg, #1e3a8a 1%, #1e40af 99%);
            color: #fff;
            padding: 24px, 20px, 32px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .header h1{
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 8px;
            padding: 8px;
            letter-spacing: 0.5px;
        }
        .total-mxn{
            font-size: 40px;
            font-weight: 700;
            margin: 8px 0;
            letter-spacing: -1px;
        }
        .total-usd {
            font-size: 20px;
            opacity: 0.85;
            font-weight: 400;
            margin-bottom: 12px
        }
        .last-update {
            font-size: 13px;
            opacity: 0.7;
            font-weight: 300;
        }

        /*CONTENEDOR PRINCIPAL*/
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        /*SECCION DE MIS ACTIVOS*/
        .section {
            padding: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        /*TARJETAS DE MIS ACTIVOS*/
        .asset-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .asset-card:active {
            transform: scale(0.98);
        }
        .asset-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        .asset-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 16px;
        }
        .asset-icon.gold {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        }
        .asset-icon.silver {
            background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
        }
        .asset-icon.bitcoin {
            background: linear-gradient(135deg, #f7931a 0%, #ffb84d 100%);
        }
        .asset-icon.ethereum {
            background: linear-gradient(135deg, #627eea 0%, #8c9eff 100%);
        }
        .asset-icon.solana {
            background: linear-gradient(135deg, #14f195 0%, #9945ff 100%);
        }
        .asset-icon.litecoin {
            background: linear-gradient(135deg, #345d9d 0%, #5a86c9 100%);
        }
        .asset-info {
            flex: 1;
        }
        .asset-name {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }
        .asset-quantity {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        .asset-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .detail-value {
            font-size: 16px;
            color: #111827;
            font-weight: 600;
        }
        .detail-value.large {
            font-size: 20px;
            color: #1e40af;
            font-weight: 700;
        }
       
        /*BOTONES DE ACCION*/
        .action-buttons {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 16px 20px;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 12px;
            max-width: 600px;
            margin: 0 auto;
        }
        .btn {
            flex: 1;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn:active{
            transform: scale(0.96);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6099f5ff 0%, #4a7be6ff 100%); /*CAMBIAR COLOR*/
            color: #fff;
        }
        .btn-secondary {
            background:  #e3e5e7ff;
            color: #374151;
        }

        /*EMPTY STATE*/
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        .empty-state-text {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .empty-state-subtext {
            font-size: 14px;
            color: #9ca3af;
        }
        /*FORMULARIO PARA AGREGAR ACTIVOS*/
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        .form-input,
        .form-select {
            width: 100%;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-submit:active {
            transform: scale(0.98);
        }
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        /*MENU DE HAMBURGUESA*/
        .menu-btn {
            position: fixed;
            top: -5px;
            right: 8px;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s;
        }
        .menu-btn.active {
            transform: scale(0.95);
        }
        .menu-btn span {
            width: 24px;
            height: 3px;
            background: #e0e0e0;
            border-radius: 2px;
            transition: all 0.3s;
        }
        .menu-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(7px, 7px);
        }
        .menu-btn.active span:nth-child(2) {
            opacity: 0;
        }
        .menu-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }
        /*OVERLAY*/
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        .menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        /*SIDEBAR*/
        .menu-sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100vh;
            background: #fff;
            z-index: 999;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -4px 0px 12px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        .menu-sidebar.active {
            right: 0;
        }
        .menu-header {
            padding: 24px 20px;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
        }
        .menu-header h2 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        .menu-nav {
            padding: 8px 0;
        }
        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            color: #374151;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }
        .menu-item:hover {
            background: #f9fafb;
        }
        .menu-item.active {
            background: #eff6ff;
            color: #2563eb;
            border-left-color: #2563eb;
        }
        .menu-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 8px 20px;
        }
        .menu-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #9ca3af;
            text-align: center;
        }
        /*TOAST NOTIFICACIONES*/
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            min-width: 280px;
            max-width: 90%;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        .toast-show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .toast-success {
            background: #059669;
            color: #fff;
        }
        .toast-warning {
            background: #f59e0b;
            color: #fff;
        }
        .toast-error {
            background: #dc2626;
            color: #fff;
        }

        /*RESPONSIVE*/
        @media (max-width: 640px) {
            .toast {
                min-width: auto;
                max-width: calc(100% - 32px);
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!--BOTON HAMBURGUESA------------------------->
    <div class="menu-btn" id="menuBtn">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!---------------OVERLAY---------------------->
    <div class="menu-overlay" id="menuOverlay"></div>

    <!-- SIDEBAR -->
    <div class="menu-sidebar" id="menuSidebar">
        <div class="menu-header">
            <h2>MetalValue</h2>
        </div>

        <nav class="menu-nav">
            <a href="{{ route('inventory.index') }}" class="menu-item {{ request()->routeIs('inventory.index') || request()->routeIs('home') ? 'active' : '' }}">
                
                Inventario
            </a>
            
            <a href="{{ route('cashflow.index') }}" class="menu-item {{ request()->routeIs('cashflow.*') ? 'active' : '' }}">
                
                Cashflow
            </a>

            <div class="menu-divider"></div>
            
            <a href="{{ route('buy.create') }}" class="menu-item {{ request()->routeIs('buy.*') ? 'active' : '' }}">
                
                Comprar
            </a>
            
            <a href="{{ route('sell.create') }}" class="menu-item {{ request()->routeIs('sell.*') ? 'active' : '' }}">
                
                Vender
            </a>

            <div class="menu-divider"></div>

            <a href="{{ route('transactions.index') }}" class="menu-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                
                Historial
            </a>
        </nav>

        <div class="menu-footer">
            MetalValue v1.0
        </div>
    </div>

    @yield('content')

    <!------TOAST-------------------->
    <x-toast />
    <script>
        const menuBtn = document.getElementById('menuBtn');
        const menuOverlay = document.getElementById('menuOverlay');
        const menuSidebar = document.getElementById('menuSidebar');

        function openMenu() {
            menuBtn.classList.add('active');
            menuOverlay.classList.add('active');
            menuSidebar.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            menuBtn.classList.remove('active');
            menuOverlay.classList.remove('active');
            menuSidebar.classList.remove('active');
            document.body.style.overflow = '';
        }

        menuBtn.addEventListener('click', () => {
            if (menuSidebar.classList.contains('active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        menuOverlay.addEventListener('click', closeMenu);
    </script>

</body>
</html>