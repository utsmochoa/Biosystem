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
<body class="bg-blue-200 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl animate-fade-in">
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
            <button id="nueva-verificacion-huella" class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-300">
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
                    <a href="{{ route('Ingreso.verificacion.cedula') }}">Presione aca para realizar una solicitud con C.I</a>
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
        let timeoutDuration = 4 * 60 * 1000; // 4 min
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

        let modalAbierto = false;
        let verificacionEnProceso = false; // Variable de control para evitar múltiples ejecuciones
        
        const modal = document.getElementById('modal-cedula');
        const inputCedula = document.getElementById('cedula');
        const formCedula = document.getElementById('form-cedula');
        
        // Función mejorada para realizar nueva verificación por huella
        function nuevaVerificacionHuella() {
            if (!modalAbierto && !verificacionEnProceso) {
                // Marcar que la verificación está en proceso
                verificacionEnProceso = true;
                
                // Obtener referencia al botón
                const botonVerificacion = document.getElementById('nueva-verificacion-huella');
                
                // Desactivar el botón visualmente
                botonVerificacion.disabled = true;
                botonVerificacion.classList.remove('hover:bg-blue-600', 'bg-blue-500');
                botonVerificacion.classList.add('bg-gray-400', 'cursor-not-allowed');
                
                // Cambiar el contenido del botón con spinner
                botonVerificacion.innerHTML = `
                    <div class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando solicitud...
                    </div>
                `;
                
                // Redirigir después de un pequeño delay para mostrar el cambio visual
                setTimeout(() => {
                    window.location.href = "{{ url('ingreso/verificar-huella') }}";
                }, 500);
            }
        }

        // Función para abrir modal de búsqueda por cédula
        function abrirModalCedula() {
            if (!verificacionEnProceso) {
                modalAbierto = true;
                if (modal) {
                    modal.classList.add('show');
                    if (inputCedula) {
                        inputCedula.focus();
                    }
                }
            }
        }

        // Función para cerrar modal
        function cerrarModal() {
            modalAbierto = false;
            if (modal) {
                modal.classList.remove('show');
            }
            if (inputCedula) {
                inputCedula.value = '';
            }
        }

        // Event listeners para botones
        document.getElementById('nueva-verificacion-huella').addEventListener('click', nuevaVerificacionHuella);
        
        const buscarPorCedulaBtn = document.getElementById('buscar-por-cedula');
        if (buscarPorCedulaBtn) {
            buscarPorCedulaBtn.addEventListener('click', abrirModalCedula);
        }
        
        const cancelarCedulaBtn = document.getElementById('cancelar-cedula');
        if (cancelarCedulaBtn) {
            cancelarCedulaBtn.addEventListener('click', cerrarModal);
        }

        // Event listener para teclas
        document.addEventListener('keydown', function(event) {
            // Solo procesar teclas si el modal no está abierto y no hay verificación en proceso
            if (!modalAbierto && !verificacionEnProceso) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    nuevaVerificacionHuella();
                } else if (event.key === ' ') {
                    event.preventDefault();
                    abrirModalCedula();
                }
            } else {
                // Si el modal está abierto, ESC para cerrar
                if (event.key === 'Escape' && modalAbierto) {
                    event.preventDefault();
                    cerrarModal();
                }
            }
        });

        // Cerrar modal al hacer clic en el fondo
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    cerrarModal();
                }
            });
        }

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

        // Solo permitir números en el input (si existe)
        if (inputCedula) {
            inputCedula.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        }

        // Validación mejorada del formulario (si existe)
        if (formCedula) {
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
        }
    </script>
</body>
</html>