<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lista de Estudiantes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->
</head>
<body class="min-h-screen bg-blue-200 flex justify-center items-center">
    <div class="bg-white shadow-md rounded-md p-6 w-full max-w-4xl">
        <div class="relative mb-2">
            <a href="{{ route('Gestion.index') }}"
               class="absolute left-0 top-1/2 transform -translate-y-1/2 text-blue-700 hover:text-blue-900 font-medium inline-flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
          
            <div class="flex flex-col items-center">
              <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-1">
              <h1 class="text-2xl font-bold text-center text-blue-700">Lista de estudiantes</h1>
            </div>
          </div>
          

        <!-- Mensajes mejorados -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-4 flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                <div>
                    <p class="font-semibold">¡Éxito!</p>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif
        
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                <div>
                    <p class="font-semibold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="p-4">
            <div class="relative mb-4">
              <input type="text" id="searchInput" placeholder="Buscar estudiante..." class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 absolute left-3 top-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Tabla de Estudiantes con el nuevo estilo -->
            <div class="bg-white p-4 shadow-md rounded-lg overflow-x-auto">
                <table class="w-full table-auto border-collapse rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-blue-500 text-white">
                            <th class="px-4 py-2 text-left rounded-tl-lg">ID</th>
                            <th class="px-4 py-2 text-left">Nombres</th>
                            <th class="px-4 py-2 text-left">Apellidos</th>
                            <th class="px-4 py-2 text-left">Cédula</th>
                            <th class="px-4 py-2 text-center rounded-tr-lg">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estudiantes as $estudiante)
                        <tr class="bg-blue-100 border-b border-blue-200 hover:bg-blue-200 
                            {{ $loop->last ? 'rounded-b-lg' : '' }}">
                            <td class="px-4 py-2">{{ $estudiante->id }}</td>
                            <td class="px-4 py-2">{{ $estudiante->nombres }}</td>
                            <td class="px-4 py-2">{{ $estudiante->apellidos }}</td>
                            <td class="px-4 py-2">{{ $estudiante->cedula_identidad }}</td>
                            <td class="px-4 py-2 text-center">
                                <button 
                                    class="bg-blue-600 text-white px-2 py-1 rounded-md hover:bg-blue-700 transition transform hover:scale-105 duration-300" 
                                    onclick="mostrarModal('{{ $estudiante->id }}', '{{ $estudiante->nombres }}', '{{ $estudiante->apellidos }}', '{{ $estudiante->cedula_identidad }}', '{{ $estudiante->carrera ?? 'No disponible' }}', '{{ $estudiante->semestre ?? 'No disponible' }}')">
                                    <i class="fas fa-eye mr-2"></i>Detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 border border-gray-200 text-center text-gray-500">
                                <i class="fas fa-users text-gray-400 text-2xl mt-2 mb-2"></i>
                                <p>No hay estudiantes registrados</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal mejorado -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 transform transition-all">
            <h2 class="text-xl font-bold text-blue-700 mb-4 flex justify-center items-center">
                <i class="fas fa-user-graduate mr-2"></i>
                Detalles del Estudiante
            </h2>
            
            <div class="space-y-3 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-id-card w-5 text-gray-500 mr-3"></i>
                    <span class="font-semibold">Cédula:</span>
                    <span id="modal-cedula" class="ml-2"></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-user w-5 text-gray-500 mr-3"></i>
                    <span class="font-semibold">Nombres:</span>
                    <span id="modal-nombres" class="ml-2"></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-user w-5 text-gray-500 mr-3"></i>
                    <span class="font-semibold">Apellidos:</span>
                    <span id="modal-apellido" class="ml-2"></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap w-5 text-gray-500 mr-3"></i>
                    <span class="font-semibold">Carrera:</span>
                    <span id="modal-carrera" class="ml-2"></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt w-5 text-gray-500 mr-3"></i>
                    <span class="font-semibold">Semestre:</span>
                    <span id="modal-semestre" class="ml-2"></span>
                </div>
            </div>
            
            <!-- Guarda el ID del estudiante para usarlo en el botón de actualizar -->
            <input type="hidden" id="estudiante-id" value="">
            
            <!-- Botones mejorados -->
            <div class="flex justify-center space-x-3">
                <button 
                    class=" mr-2 bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition-colors duration-300 transform hover:scale-105 flex items-center"
                    onclick="actualizarHuella()">
                    <i class="fas fa-fingerprint mr-2"></i>Actualizar Huella
                </button>
                
                <button 
                    class="mr-2 bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors duration-300 transform hover:scale-105 flex items-center"
                    onclick="irAActualizarDatos()">
                    <i class="fas fa-edit mr-2"></i>Actualizar Datos
                </button>
                
                <button 
                    class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors duration-300 transform hover:scale-105 flex items-center "
                    onclick="cerrarModal()">
                    <i class="fas fa-times mr-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Overlay de carga -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="text-lg">Procesando captura de huella...</span>
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

        
        // Variable para controlar el estado del botón
        let isUpdatingFingerprint = false;

        function mostrarModal(id, nombres, apellidos, cedula, carrera, semestre) {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('estudiante-id').value = id;
            document.getElementById('modal-cedula').innerText = cedula;
            document.getElementById('modal-nombres').innerText = nombres;
            document.getElementById('modal-apellido').innerText = apellidos;
            document.getElementById('modal-carrera').innerText = carrera;
            document.getElementById('modal-semestre').innerText = semestre;
        }

        function cerrarModal() {
            document.getElementById('modal').classList.add('hidden');
        }
        
        function irAActualizarDatos() {
            const id = document.getElementById('estudiante-id').value;
            const nombres = document.getElementById('modal-nombres').innerText;
            const apellidos = document.getElementById('modal-apellido').innerText;
            const cedula = document.getElementById('modal-cedula').innerText;
            const carrera = document.getElementById('modal-carrera').innerText;
            const semestre = document.getElementById('modal-semestre').innerText;
            
            window.location.href = `/admin/gestion-estudiante/actualizar-estudiante/${id}/actualizar-datos?nombres=${encodeURIComponent(nombres)}&apellidos=${encodeURIComponent(apellidos)}&cedula=${encodeURIComponent(cedula)}&carrera=${encodeURIComponent(carrera)}&semestre=${encodeURIComponent(semestre)}`;
        }

        function actualizarHuella() {
            // Si ya está en proceso, no hacer nada
            if (isUpdatingFingerprint) return;
            
            // Marcar que estamos en proceso
            isUpdatingFingerprint = true;
            
            const id = document.getElementById('estudiante-id').value;
            
            // Deshabilitar botón visualmente
            const fingerprintBtn = document.querySelector('button[onclick="actualizarHuella()"]');
            if (fingerprintBtn) {
                fingerprintBtn.disabled = true;
                fingerprintBtn.classList.add('opacity-50', 'cursor-not-allowed');
                fingerprintBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
            }
            
            // Mostrar overlay de carga
            document.getElementById('loadingOverlay').classList.remove('hidden');
            cerrarModal();
            
            // Crear formulario dinámico y enviarlo
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("capturar.huella") }}';
            
            // Token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // ID del estudiante
            const estudianteInput = document.createElement('input');
            estudianteInput.type = 'hidden';
            estudianteInput.name = 'estudiante_id';
            estudianteInput.value = id;
            form.appendChild(estudianteInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Auto-ocultar mensajes después de 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
        
        // Filtro de búsqueda 
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            
            searchInput.addEventListener('keyup', function() {
                const searchText = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const id = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const nombres = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const apellidos = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const cedula = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    
                    if (id.includes(searchText) || nombres.includes(searchText) || 
                        apellidos.includes(searchText) || cedula.includes(searchText)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>