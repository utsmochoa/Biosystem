<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Estudiante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .profile-img {
            width: 128px;
            height: 128px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
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
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <!-- Foto de perfil -->
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <img src="data:image/jpeg;base64,{{ base64_encode($estudiante->foto) }}" 
                         alt="Foto del estudiante" 
                         class="profile-img">
                </div>
                <div>
                    @if($estudiante->deuda >= 1)
                        <h1 class="text-2xl font-bold bg-red-500 text-white px-4 py-2 rounded-lg inline-block">Insolvente</h1>
                        <p class="text-gray-600">Debe: {{ $estudiante->deuda }} bs.</p>
                    @else
                        <h1 class="text-2xl font-bold bg-green-500 text-white px-4 py-2 rounded-lg inline-block">Solvente</h1>
                        <p class="text-gray-600">Debe: {{ $estudiante->deuda}} bs.</p>
                    @endif
                </div>
            </div>
            <!-- Botón de solicitud por huella -->
            <button id="nueva-verificacion-huella" class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                Presione [ENTER] para una nueva solicitud
            </button>
        </div>

        <!-- Información del estudiante -->
        <div class="mb-6 grid grid-cols-2 gap-4 text-gray-800">
            <div>
                <p><strong>Nombres:</strong> {{ $estudiante->nombres }}</p>
                <p><strong>Apellidos:</strong> {{ $estudiante->apellidos }}</p>
                <p><strong>Cédula de identidad:</strong> {{ $estudiante->cedula_identidad }}</p>
            </div>
            <div>
                <p><strong>Carrera:</strong> {{ $estudiante->carrera }}</p>
                <p><strong>Semestre:</strong> {{ $estudiante->semestre }}</p>
            </div>
        </div>

        <div class="bg-blue-100 p-4 rounded-lg shadow-inner">
            <h2 class="text-xl font-bold text-center mb-4">Opciones de Consulta</h2>
            <div class="flex flex-col items-center space-y-2">
                <button id="buscar-por-cedula" class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                    Presione [ESPACIO] para realizar una solicitud con C.I
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para búsqueda por cédula -->
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
                            class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancelar
                    </button>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-3 text-center">
                Presione ESC para cancelar
            </p>
        </div>
    </div>

    <!-- Mostrar mensajes -->
    @if(session('error'))
        <div id="error-message" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif
    
    @if(session('success'))
        <div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <script>
        let modalAbierto = false;
        const modal = document.getElementById('modal-cedula');
        const inputCedula = document.getElementById('cedula');
        const formCedula = document.getElementById('form-cedula');
        
        // Función para realizar nueva verificación por huella
        function nuevaVerificacionHuella() {
            if (!modalAbierto) {
                window.location.href = "{{ url('ingreso/verificar-huella') }}";
            }
        }

        // Función para abrir modal de búsqueda por cédula
        function abrirModalCedula() {
            modalAbierto = true;
            modal.classList.add('show');
            inputCedula.focus();
        }

        // Función para cerrar modal
        function cerrarModal() {
            modalAbierto = false;
            modal.classList.remove('show');
            inputCedula.value = '';
        }

        // Event listeners para botones
        document.getElementById('nueva-verificacion-huella').addEventListener('click', nuevaVerificacionHuella);
        document.getElementById('buscar-por-cedula').addEventListener('click', abrirModalCedula);
        document.getElementById('cancelar-cedula').addEventListener('click', cerrarModal);

        // Event listener para teclas
        document.addEventListener('keydown', function(event) {
            // Solo procesar teclas si el modal no está abierto
            if (!modalAbierto) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    nuevaVerificacionHuella();
                } else if (event.key === ' ') {
                    event.preventDefault();
                    abrirModalCedula();
                }
            } else {
                // Si el modal está abierto, ESC para cerrar
                if (event.key === 'Escape') {
                    event.preventDefault();
                    cerrarModal();
                }
            }
        });

        // Cerrar modal al hacer clic en el fondo
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                cerrarModal();
            }
        });

        // Auto-cerrar mensajes después de 5 segundos
        const errorMessage = document.getElementById('error-message');
        const successMessage = document.getElementById('success-message');
        
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }
        
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }

        // Abrir modal automáticamente si hay un error de cédula
        @if(session('error') && old('cedula'))
            setTimeout(() => {
                abrirModalCedula();
            }, 100);
        @endif

        // Solo permitir números en el input
        inputCedula.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Validación mejorada del formulario
        formCedula.addEventListener('submit', function(event) {
            const cedula = inputCedula.value.trim();
            
            if (cedula.length < 6) {
                event.preventDefault();
                alert('La cédula debe tener al menos 6 dígitos');
                return false;
            }
            
            if (cedula.length > 12) {
                event.preventDefault();
                alert('La cédula no puede tener más de 12 dígitos');
                return false;
            }
        });
    </script>
</body>
</html>