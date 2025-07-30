<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BioSystem | Gestionar Estudiantes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->
</head>

<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-md rounded-md p-10 w-full max-w-2xl mx-auto animate-fade-in">

        <div class="relative mb-2">
            <a href="{{ route('Admin.index') }}"
               class="absolute left-0 top-4 transform -translate-y-1/2 text-blue-700 hover:text-blue-900 font-medium inline-flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>

            <!-- Boton invisible para gestion de deudas -->
            <a href="{{ route('gestionar.deudas') }}"
               class="absolute right-0 top-0 w-8 h-8 opacity-0 hover:opacity-20 bg-white rounded-full transition-opacity duration-300"
               title="Gestión de deudas">
            </a>

            <!-- Encabezado -->
            <div class="flex flex-col items-center mb-10">
                <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-4">
                <h1 class="text-3xl font-extrabold text-blue-800 tracking-wide text-center">
                    Gestionar Estudiantes
                </h1>
                <p class="text-lg text-gray-600 mt-2 mb-4 text-center">Selecciona una acción</p>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col items-center gap-2">
            <a href="{{ route('Gestion.seleccion') }}"
               class="w-64 py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold rounded-full shadow-md transition duration-300 transform hover:scale-105">
                <i class="fas fa-user-plus mr-2"></i> Agregar estudiante
            </a>

            <a href="{{ route('Gestion.actualizar') }}"
               class="w-64 py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold rounded-full shadow-md transition duration-300 transform hover:scale-105">
                <i class="fas fa-user-edit mr-2"></i> Actualizar estudiante
            </a>

            <a href="{{ route('Gestion.eliminar') }}"
               class="w-64 py-3 px-6 bg-blue-600 hover:bg-red-700 text-white text-center font-semibold rounded-full shadow-md transition duration-300 transform hover:scale-105">
                <i class="fas fa-user-times mr-2"></i> Deshabilitar estudiante
            </a>
        </div>
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
    

    <!-- Script para hacer el backdoor más divertido -->
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


        // Hacer que el backdoor sea más visible cuando se pasa el mouse por la esquina
        document.addEventListener('DOMContentLoaded', function() {
            const backdoor = document.querySelector('a[title="🤫 Gestión de deudas"]');
            let clickCount = 0;
            
            // Efecto especial después de varios clics en cualquier parte
            document.addEventListener('click', function() {
                clickCount++;
                if (clickCount === 10) {
                    backdoor.style.opacity = '0.3';
                    backdoor.style.animation = 'pulse 1s infinite';
                    
                    // Agregar animación CSS
                    const style = document.createElement('style');
                    style.textContent = `
                        @keyframes pulse {
                            0% { transform: scale(1); }
                            50% { transform: scale(1.2); }
                            100% { transform: scale(1); }
                        }
                    `;
                    document.head.appendChild(style);
                }
            });
        });
    </script>
</body>
</html>