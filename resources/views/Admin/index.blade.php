<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BioSystem | Ingreso</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->
</head>

<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-md rounded-md p-10 w-full max-w-2xl mx-auto animate-fade-in hover:shadow-xl hover:-translate-y-1 transition duration-300">

        <!-- Encabezado -->
        <div class="flex flex-col items-center mb-10">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-4">
            <h1 class="text-3xl font-extrabold text-blue-800 tracking-wide text-center">
                Bienvenido/a, {{ auth()->user()->name }}
            </h1>
            
            <p class="text-lg text-gray-600 mt-2 mb-4 text-center">¿Qué deseas hacer hoy?</p>
        </div>

        <!-- Botones principales -->
        <div class="flex flex-col md:flex-row gap-6 justify-center mb-8">
            <button onclick="window.location.href='{{ route('Gestion.index') }}'" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-full shadow-md transition transform hover:scale-105 duration-300">
                <i class="fas fa-user-graduate mr-2"></i> Gestionar Estudiantes
            </button>

            <button onclick="window.location.href='{{ route('Configurar.index') }}'" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-full shadow-md transition transform hover:scale-105 duration-300">
                <i class="fas fa-cogs mr-2"></i> Configurar Sistema
            </button>
        </div>

        <!-- Botón de logout -->
        <form method="POST" action="{{ route('logout') }}" class="flex justify-center">
            @csrf
            <button type="submit" class="text-sm text-red-600 hover:underline hover:text-red-800 transition">
                <i class="fas fa-sign-out-alt mr-1"></i> Cerrar sesión
            </button>
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
    </script>
    
    
    

</body>



</html>
