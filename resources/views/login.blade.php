<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BioSystem | Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->
</head>
<body class="bg-blue-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-md shadow-md p-10 w-full max-w-lg animate-fade-in">
        
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-4">
            <h1 class="text-3xl font-extrabold text-blue-800 tracking-wide text-center">Iniciar sesión</h1>
            <p class="text-sm text-gray-500 text-center mt-2">Accede a tu cuenta de forma segura</p>
        </div>

        @if(session('logout_reason'))
            <div class="flex items-center bg-red-100 border border-red-400 text-red-800 text-sm font-semibold px-6 py-4 rounded-lg shadow-md mb-6 animate-fade-in">
                <i class="fas fa-exclamation-triangle text-xl mr-3 text-yellow-300 animate-bounce"></i>
                <span class="text-center w-full">{{ session('logout_reason') }}</span>
            </div>
        @endif

        <div class="flex flex-col items-center space-y-4">
            <button id="huella-btn" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all duration-300" style="min-width: 280px;">
                <i class="fas fa-fingerprint mr-2"></i> Iniciar sesión con huella
            </button>
            <button id="password-btn" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all duration-300" style="min-width: 280px;">
                <i class="fas fa-key mr-2"></i> Iniciar sesión con contraseña
            </button>
        </div>
    </div>

    <script>
        let procesoEnCurso = false;

        // Función para resetear botones al cargar la página
        function resetearBotones() {
            const botonHuella = document.getElementById('huella-btn');
            const botonPassword = document.getElementById('password-btn');
            
            // Resetear estado de procesamiento
            procesoEnCurso = false;
            
            // Restaurar botón de huella
            botonHuella.disabled = false;
            botonHuella.classList.remove('bg-gray-400', 'cursor-not-allowed');
            botonHuella.classList.add('bg-blue-600', 'hover:bg-blue-700');
            botonHuella.innerHTML = '<i class="fas fa-fingerprint mr-2"></i> Iniciar sesión con huella';
            
            // Restaurar botón de contraseña
            botonPassword.disabled = false;
            botonPassword.classList.remove('bg-gray-400', 'cursor-not-allowed');
            botonPassword.classList.add('bg-blue-600', 'hover:bg-blue-700');
            botonPassword.innerHTML = '<i class="fas fa-key mr-2"></i> Iniciar sesión con contraseña';
        }

        // Función para manejar login con huella
        function loginConHuella() {
            if (!procesoEnCurso) {
                procesoEnCurso = true;
                
                const botonHuella = document.getElementById('huella-btn');
                
                // Desactivar botón
                botonHuella.disabled = true;
                botonHuella.classList.remove('hover:bg-blue-700', 'bg-blue-600');
                botonHuella.classList.add('bg-gray-400', 'cursor-not-allowed');
                
                // Cambiar contenido con spinner
                botonHuella.innerHTML = `
                    <div class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando autenticación...
                    </div>
                `;
                
                // Redirigir después de mostrar el cambio visual
                setTimeout(() => {
                    window.location.href = '{{ route('authenticate.fingerprint') }}';
                }, 500);
            }
        }

        // Función para manejar login con contraseña
        function loginConPassword() {
            if (!procesoEnCurso) {
                procesoEnCurso = true;
                
                const botonPassword = document.getElementById('password-btn');
                
                // Desactivar botón
                botonPassword.disabled = true;
                botonPassword.classList.remove('hover:bg-blue-700', 'bg-blue-600');
                botonPassword.classList.add('bg-gray-400', 'cursor-not-allowed');
                
                // Cambiar contenido con spinner
                botonPassword.innerHTML = `
                    <div class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Redirigiendo...
                    </div>
                `;
                
                // Redirigir después de mostrar el cambio visual
                setTimeout(() => {
                    window.location.href = '{{ route('credenciales') }}';
                }, 500);
            }
        }

        // Resetear botones cuando se carga la página
        window.addEventListener('load', resetearBotones);
        
        // Resetear botones cuando se regresa a la página (navegador)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                resetearBotones();
            }
        });

        // Event listeners para los botones
        document.getElementById('huella-btn').addEventListener('click', loginConHuella);
        document.getElementById('password-btn').addEventListener('click', loginConPassword);
    </script>
</body>
</html>