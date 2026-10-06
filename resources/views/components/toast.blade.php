@if(session()->has('success') || session()->has('warning') || session()->has('error'))
    <div id="toast" class="toast {{ session()->has('success') ? 'toast-success' : (session()->has('warning') ? 'toast-warning' : 'toast-error') }}">
        {{ session('success') ?? session('warning') ?? session('error') }}
    </div>

    <script>
        (function() {
            const toast = document.getElementById('toast');
            
            // Mostrar el toast con animación
            setTimeout(() => {
                toast.classList.add('toast-show');
            }, 100);

            // Ocultar después de 3 segundos
            setTimeout(() => {
                toast.classList.remove('toast-show');
                
                // Eliminar del DOM después de la animación
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        })();
    </script>
@endif