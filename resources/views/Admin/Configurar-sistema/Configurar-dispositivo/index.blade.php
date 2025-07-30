<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>BioSystem | Configuración de dispositivo biométrico</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>
<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-md rounded-md p-10 w-full animate-fade-in max-w-2xl mx-auto">
        <div class="relative mb-2">
            <a href="{{ route('Configurar.index') }}"
               class="absolute left-0 top-4 transform -translate-y-1/2 text-blue-700 hover:text-blue-900 font-medium inline-flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>

            <!-- Encabezado -->
            <div class="flex flex-col items-center mb-10">
                <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-4" />
                <h1 class="text-3xl font-extrabold text-blue-800 tracking-wide text-center">
                    Configuración de dispositivo biométrico
                </h1>
                <p class="text-lg text-gray-600 mt-2 mb-4 text-center">Selecciona una acción para tu dispositivo</p>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Éxito!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col items-center gap-2">
            <a href="{{ route('Ver.dispositivos') }}"
            class="py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full shadow-md transition duration-300 transform hover:scale-105 text-center">
                <i class="fas fa-plug mr-2"></i> Ver dispositivos conectados
            </a>


            <!-- Botón con protección contra múltiples clics -->
            <form id="probar-form" action="{{ route('Probar.dispositivo') }}" method="GET" onsubmit="return bloquearBoton()">
                @csrf
                <button id="btn-probar"
                    type="submit"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold rounded-full shadow-md transition duration-300 transform hover:scale-105s">
                    <i class="fas fa-vial mr-2"></i> Probar dispositivo biométrico
                </button>
            </form>
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

    <script>
        // Protección contra spam de clics
        let botonProbarActivo = true;

        function bloquearBoton() {
            if (!botonProbarActivo) return false;

            botonProbarActivo = false;
            const btn = document.getElementById('btn-probar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Ejecutando...';
            return true;
        }

        // Manejo de inactividad
        let timeoutDuration = 2 * 60 * 1000;
        let warningDuration = 1 * 60 * 1000;
        let warningTimer, logoutTimer;

        function startTimers() {
            warningTimer = setTimeout(() => {
                document.getElementById('inactivity-modal').classList.remove('hidden');
            }, warningDuration);

            logoutTimer = setTimeout(() => {
                document.getElementById('inactive').value = '1';
                document.getElementById('logout-form').submit();
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

        window.addEventListener('DOMContentLoaded', () => {
            startTimers();
        });

        ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
            window.addEventListener(evt, () => {
                resetTimers();
                closeModal();
            });
        });
    </script>
</body>
</html>
