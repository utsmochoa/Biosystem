<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Administrador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-12">
            <h1 class="text-xl font-bold text-blue-700 pr-24">Agregar administrador</h1>
        </div>
        <p class="text-gray-600 text-sm mb-6">Llene el formulario con los datos del administrador y luego presione siguiente</p>
        
        @if (session('error'))
            <div class="flex items-start space-x-3 bg-red-50 border border-red-200 rounded-xl p-4 shadow-md mb-6">
                <div class="flex-shrink-0">
                    <i class="fas fa-circle-exclamation text-red-500 text-2xl"></i>
                </div>
                <div class="flex-1 text-sm text-red-800">
                    <p class="font-bold text-base mb-1">¡Error!</p>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('Agregar.admin.pedir-huella') }}" method="post">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-semibold text-gray-700">Nombre de usuario:</label>
                <input type="text" id="name" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div>

           <div class="mb-4">
                <label for="password" class="block text-sm font-semibold text-gray-700">Contraseña:</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div> 

            {{-- <div class="mb-4">
                <label for="confirmar" class="block text-sm font-semibold text-gray-700">Confirmar contraseña:</label>
                <input type="text" id="confirmar" name="confirmar" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div> --}}

            <div class="flex justify-between items-center mt-6">
                <button type="button" onclick="window.location.href='{{ route('Crear.roles') }}'" 
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                  Cancelar
                </button>
              
                <div class="flex space-x-4">
                  <button type="reset" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                    Vaciar
                  </button>
                  <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                    Siguiente
                  </button>
                </div>
              </div>  
        </form>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="inactive" id="inactive" value="0">
    </form>
    
    
    
    <div id="inactivity-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full text-center">
            <h2 class="text-xl font-bold text-gray-800 mb-2">⚠ Inactividad detectada</h2>
            <p class="text-gray-600 mb-4">Tu sesión se cerrará automáticamente en 1 minuto.</p>
            <button onclick="closeModal()" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Entendido
            </button>
        </div>
    </div>
</body>




<script>
    let timeoutDuration = 2 * 60 * 1000; // 2 min
    let warningDuration = 1 * 60 * 1000; // 1 min

    let warningTimer, logoutTimer;

    function startTimers() {
        warningTimer = setTimeout(() => {
            document.getElementById('inactivity-modal').classList.remove('hidden');
        }, warningDuration);

        logoutTimer = setTimeout(() => {
            document.getElementById('inactive').value = '1';
            document.getElementById('logout-form').submit(); // se hace POST correctamente
        }, timeoutDuration);


    }

    function resetTimers() {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        startTimers();
    }

    function closeModal() {
        document.getElementById('inactivity-modal').classList.add('hidden');
    }

    // Inicia los temporizadores al cargar la página
    window.addEventListener('DOMContentLoaded', () => {
        startTimers();
    });

    // Resetea los temporizadores con actividad del usuario
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
        window.addEventListener(evt, () => {
            resetTimers();
            closeModal(); // Solo se cierra si el usuario se mueve
        });
    });

  // Oculta el mensaje de error tras 5 segundos
  setTimeout(() => {
      const alert = document.querySelector('.bg-red-50.border-red-200');
      if (alert) {
          alert.classList.add('opacity-0', 'transition-opacity', 'duration-1000');
          setTimeout(() => alert.remove(), 1000); // Remover del DOM tras animarse
      }
  }, 5000);
</script>
</html>