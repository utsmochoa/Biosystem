<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación por Cédula - Estado del Estudiante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">

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
        .loader {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <!-- Foto de perfil y estado de solvencia -->
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @if(isset($estudiante))
                        <img src="data:image/jpeg;base64,{{ base64_encode($estudiante->foto) }}" 
                             alt="Foto del estudiante" 
                             class="profile-img">
                    @else
                        <div class="profile-img bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-user text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                </div>
                <div>
                    @if(isset($estudiante))
                        @if($estudiante->deuda >= 1)
                            <h1 class="text-2xl font-bold bg-red-500 text-white px-4 py-2 rounded-lg inline-block">Insolvente</h1>
                            <p class="text-gray-600">Debe: {{ $estudiante->deuda }} bs.</p>
                        @else
                            <h1 class="text-2xl font-bold bg-green-500 text-white px-4 py-2 rounded-lg inline-block">Solvente</h1>
                            <p class="text-gray-600">Debe: {{ $estudiante->deuda}} bs.</p>
                        @endif
                    @else
                        <h1 class="text-2xl font-bold bg-gray-500 text-white px-4 py-2 rounded-lg inline-block">Ingrese Cédula</h1>
                        <p class="text-gray-600">Esperando verificación...</p>
                    @endif
                </div>
            </div>
            
            <!-- Input para nueva búsqueda por cédula -->
            <div class="flex flex-col items-end space-y-2">
                <form id="form-cedula-principal" action="{{ route('buscar.cedula.verificacion') }}" method="POST" class="flex items-center space-x-2">
                    @csrf
                    <input type="text" 
                           id="cedula-principal" 
                           name="cedula" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ingrese cédula..."
                           value="{{ old('cedula') }}"
                           required
                           autocomplete="off">
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Verificar
                    </button>
                </form>
                <p class="text-xs text-gray-500">Ingrese la cédula y presione ENTER</p>
            </div>
        </div>

        <!-- Información del estudiante -->
        <div class="mb-6 grid grid-cols-2 gap-4 text-gray-800">
            @if(isset($estudiante))
                <div>
                    <p><strong>Nombres:</strong> {{ $estudiante->nombres }}</p>
                    <p><strong>Apellidos:</strong> {{ $estudiante->apellidos }}</p>
                    <p><strong>Cédula de identidad:</strong> {{ $estudiante->cedula_identidad }}</p>
                </div>
                <div>
                    <p><strong>Carrera:</strong> {{ $estudiante->carrera }}</p>
                    <p><strong>Semestre:</strong> {{ $estudiante->semestre }}</p>
                </div>
            @else
                <div>
                    <p><strong>Nombres:</strong> <span class="text-gray-400">---</span></p>
                    <p><strong>Apellidos:</strong> <span class="text-gray-400">---</span></p>
                    <p><strong>Cédula de identidad:</strong> <span class="text-gray-400">---</span></p>
                </div>
                <div>
                    <p><strong>Carrera:</strong> <span class="text-gray-400">---</span></p>
                    <p><strong>Semestre:</strong> <span class="text-gray-400">---</span></p>
                </div>
            @endif
        </div>

        <div class="bg-blue-100 p-4 rounded-lg shadow-inner">
            <h2 class="text-xl font-bold text-center mb-4">Opciones de Consulta</h2>
            <div class="flex flex-col items-center space-y-2">
                <button id="buscar-por-huella" class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                    <a href="{{ url('ingreso/verificar-huella') }}" id="huella-link">Presione aca para realizar una verificacion por huella</a>
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

        let modalAbierto = false;
        const modal = document.getElementById('modal-cedula');
        const inputCedulaModal = document.getElementById('cedula');
        const inputCedulaPrincipal = document.getElementById('cedula-principal');
        const formCedulaModal = document.getElementById('form-cedula-modal');
        const formCedulaPrincipal = document.getElementById('form-cedula-principal');
        const botonHuella = document.getElementById('buscar-por-huella');
        const linkHuella = document.getElementById('huella-link');
        let huellaProcesando = false;

         // Auto-enfoque en el input
         document.getElementById('cedula-principal').focus();
        
        // Función para ir a verificación por huella
        function irVerificacionHuella() {
            if (!modalAbierto && !huellaProcesando) {
                huellaProcesando = true;
                
                // Cambiar el texto del botón y deshabilitarlo
                botonHuella.innerHTML = '<span class="loader"></span> Procesando solicitud...';
                botonHuella.classList.add('opacity-50', 'cursor-not-allowed');
                botonHuella.disabled = true;
                
                // Redirigir después de un pequeño retraso para que se vea el cambio
                setTimeout(() => {
                    window.location.href = linkHuella.getAttribute('href');
                }, 100);
            }
        }

        // Función para abrir modal de búsqueda por cédula
        function abrirModalCedula() {
            modalAbierto = true;
            modal.classList.add('show');
            inputCedulaModal.focus();
        }

        // Función para cerrar modal
        function cerrarModal() {
            modalAbierto = false;
            modal.classList.remove('show');
            inputCedulaModal.value = '';
            inputCedulaPrincipal.focus();
        }

        // Event listeners para botones
        botonHuella.addEventListener('click', function(e) {
            e.preventDefault();
            irVerificacionHuella();
        });
        
        document.getElementById('buscar-por-cedula-modal').addEventListener('click', abrirModalCedula);
        document.getElementById('cancelar-cedula').addEventListener('click', cerrarModal);

        // Event listener para teclas
        document.addEventListener('keydown', function(event) {
            // Solo procesar teclas si el modal no está abierto
            if (!modalAbierto) {
                if (event.key === ' ') {
                    event.preventDefault();
                    irVerificacionHuella();
                } else if (event.key.toLowerCase() === 'm') {
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

        // Solo permitir números en los inputs
        [inputCedulaPrincipal, inputCedulaModal].forEach(input => {
            if (input) {
                input.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                });
            }
        });

        // Validación mejorada de los formularios
        [formCedulaPrincipal, formCedulaModal].forEach(form => {
            if (form) {
                form.addEventListener('submit', function(event) {
                    const input = form.querySelector('input[name="cedula"]');
                    const cedula = input.value.trim();
                    
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
        });

        // Enfocar automáticamente el input principal al cargar la página
        window.addEventListener('load', function() {
            if (inputCedulaPrincipal) {
                inputCedulaPrincipal.focus();
            }
        });

        // Si hay error, limpiar el input principal y mantener focus
        @if(session('error'))
            setTimeout(() => {
                if (inputCedulaPrincipal) {
                    inputCedulaPrincipal.value = '';
                    inputCedulaPrincipal.focus();
                }
            }, 100);
        @endif
    </script>

    <!-- Incluir FontAwesome si no está ya incluido -->
</body>
</html>