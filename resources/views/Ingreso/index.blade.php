<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioSystem | Ingreso por Huella</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-disabled {
        opacity: 0.7;
        cursor: not-allowed;
        pointer-events: none;
        }
        .hidden {
            display: none;
        }
    </style>
</head>

<body class="bg-blue-200 flex items-center justify-center min-h-screen">
    <div class="bg-white p-10 rounded-lg shadow-lg text-center w-full max-w-md relative animate-fade-in">
        <!-- Logo -->
        <div class="mb-6">
            <img src="/images/logo.png" alt="Logo IUTAJS" class="w-24 mx-auto">
        </div>

        <!-- Título -->
        <h1 class="text-2xl font-extrabold text-blue-800 mb-6">Sistema de Ingreso por Tecnología Dactilar</h1>

        <p class="text-gray-700 text-sm">Presione el botón para iniciar el proceso de verificación</p>
        <p class="text-gray-500 text-xs mb-4">Asegúrese de que su huella esté limpia y seca</p>

        <!-- Botones -->
        <div class=" flex flex-col items-center space-y-4">
            <a id="btn-verificar-huella"
            href="{{ url('ingreso/verificar-huella') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full shadow text-sm font-semibold transition transform hover:scale-105 duration-300"
            onclick="return verificarHuella(this);">
                <i class="fas fa-fingerprint mr-2 text-xl"></i> <span id="btn-text">Verificar Huella</span>
                <span id="btn-loading" class="hidden">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Procesando...
                </span>
            </a>

            <a id="btn-ir-cedula"
               href="{{ route('Ingreso.verificacion.cedula') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full shadow text-sm font-semibold transition transform hover:scale-105 duration-300">
                <i class="fas fa-id-card mr-2"></i> Verificar Cédula
            </a>

        

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="mt-4 text-red-600 hover:text-red-800 font-semibold transition text-sm underline">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesión
                </button>
            </form>
        </div>
        
    
    </div>

    <!-- Modal de cédula -->
    <div id="modal-cedula" class="modal">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
            <h3 class="text-lg font-bold mb-4 text-center">Buscar por Cédula de Identidad</h3>
            <form id="form-cedula" action="{{ route('buscar.cedula') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="cedula" class="block text-sm font-medium text-gray-700 mb-2">
                        Ingrese la cédula de identidad:
                    </label>
                    <input type="text" 
                           id="cedula" 
                           name="cedula" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ej: 12345678"
                           value="{{ old('cedula') }}"
                           required>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Buscar
                    </button>
                    <button type="button" 
                            id="cancelar-cedula"
                            class="flex-1 ml-2 bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancelar
                    </button>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-3 text-center">
                Presione ESC para cancelar
            </p>
        </div>
    </div>

    <!-- Mensajes -->
    @if(session('error'))
        <div id="error-message" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            <strong>Éxito:</strong> {{ session('success') }}
        </div>
    @endif

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

    <!-- Script -->
    <script>
        let timeoutDuration = 4 * 60 * 1000; // 2 min
        let warningDuration = 3 * 60 * 1000; // 1 min
    
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

        const modal = document.getElementById('modal-cedula');
        const cedulaInput = document.getElementById('cedula');
        let modalAbierto = false;
        let huellaVerificando = false;
    
        function abrirModal() {
            modalAbierto = true;
            modal.classList.add('show');
            cedulaInput.focus();
        }
    
        function cerrarModal() {
            modalAbierto = false;
            modal.classList.remove('show');
            cedulaInput.value = '';
        }
    
        // Función para manejar la verificación de huella
        function verificarHuella(btn) {
            if (huellaVerificando) return false;
            
            huellaVerificando = true;
            btn.classList.add('btn-disabled');
            document.getElementById('btn-text').classList.add('hidden');
            document.getElementById('btn-loading').classList.remove('hidden');
            
        
            
            return true;
        }
    
        // Botones
        document.getElementById('btn-buscar-cedula').addEventListener('click', abrirModal);
        document.getElementById('cancelar-cedula').addEventListener('click', cerrarModal);
        document.getElementById('modal-cedula').addEventListener('click', e => {
            if (e.target === modal) cerrarModal();
        });
    
        // Teclas
        document.addEventListener('keydown', function(e) {
            if (!modalAbierto) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const btn = document.getElementById('btn-verificar-huella');
                    if (!huellaVerificando) {
                        verificarHuella(btn);
                    }
                } else if (e.key === ' ') {
                    e.preventDefault();
                    abrirModal();
                } else if (e.key.toLowerCase() === 'c') {
                    e.preventDefault();
                    window.location.href = "{{ route('Ingreso.verificacion.cedula') }}";
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cerrarModal();
            }
        });
    
        // Solo números en cédula
        cedulaInput.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    
        // Validación cédula
        document.getElementById('form-cedula').addEventListener('submit', function(e) {
            const cedula = cedulaInput.value.trim();
            if (cedula.length < 6 || cedula.length > 12) {
                e.preventDefault();
                alert('La cédula debe tener entre 6 y 12 dígitos');
            }
        });
    
        // Auto-cierre de alertas
        if (document.getElementById('error-message')) {
            setTimeout(() => document.getElementById('error-message').remove(), 5000);
        }
        if (document.getElementById('success-message')) {
            setTimeout(() => document.getElementById('success-message').remove(), 5000);
        }
    
        // Auto-abrir si hay error en cédula
        @if(session('error') && old('cedula'))
            setTimeout(() => abrirModal(), 100);
        @endif
    </script>
</body>
</html>