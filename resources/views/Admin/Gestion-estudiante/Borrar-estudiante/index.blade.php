<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Estudiantes</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->

</head>
<body class="min-h-screen bg-blue-200 flex justify-center items-center">
  <div class="bg-white shadow-md rounded-md p-6 w-full max-w-6xl">
    <!-- Header -->
    <div class="relative mb-4">
      <a href="{{ route('Gestion.index') }}"
         class="absolute left-0 top-1/2 transform -translate-y-1/2 text-blue-700 hover:text-blue-900 font-medium inline-flex items-center transition">
          <i class="fas fa-arrow-left mr-2"></i> Volver
      </a>
    
      <div class="flex flex-col items-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-1">
        <h1 class="text-2xl font-bold text-center text-blue-700">Gestión de Estudiantes</h1>
      </div>
    </div>

    <!-- Alerta de éxito -->
    @if(session('success'))
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 mx-4">
        {{ session('success') }}
      </div>
    @endif

    <!-- Pestañas -->
    <div class="mb-4">
      <div class="flex justify-center border-b border-gray-200">
        <button 
          class="tab-button py-2 px-6 text-sm font-medium text-blue-600 bg-blue-50 border-b-2 border-blue-600 rounded-t-lg active"
          data-tab="habilitados">
          <i class="fas fa-user-check mr-2"></i>Estudiantes Habilitados
        </button>
        <button 
          class="tab-button py-2 px-6 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-t-lg ml-2"
          data-tab="deshabilitados">
          <i class="fas fa-user-times mr-2"></i>Estudiantes Deshabilitados
        </button>
      </div>
    </div>

    <!-- Buscar -->
    <div class="p-4">
      <div class="relative mb-4">
        <input type="text" id="searchInput" placeholder="Buscar estudiante..." class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500 transition duration-300 ease-in-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 absolute left-3 top-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      
      <!-- Tabla de estudiantes habilitados -->
      <div id="tabla-habilitados" class="tab-content bg-white p-4 rounded-lg shadow-md overflow-x-auto">
        <h3 class="text-lg text-center font-semibold text-blue-700 mb-3">
          <i class="fas fa-user-check mr-2"></i>Estudiantes Habilitados
        </h3>
        <table class="w-full table-auto border-collapse">
          <thead>
            <tr class="bg-blue-500 text-white">
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Nombres</th>
              <th class="px-4 py-2 text-left">Apellidos</th>
              <th class="px-4 py-2 text-left">Cédula de Identidad</th>
              <th class="px-4 py-2 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($estudiantesHabilitados as $estudiante)
              <tr class="bg-blue-100 border-b border-blue-200 hover:bg-blue-200">
                <td class="px-4 py-2">{{ $estudiante->id }}</td>
                <td class="px-4 py-2">{{ $estudiante->nombres }}</td>
                <td class="px-4 py-2">{{ $estudiante->apellidos }}</td>
                <td class="px-4 py-2">{{ $estudiante->cedula_identidad }}</td>
                <td class="px-4 py-2 text-center">
                  <div class="flex justify-center space-x-2">
                    <button 
                      class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition transform hover:scale-105 duration-300"
                      onclick="mostrarModalDetalles('{{ $estudiante->nombres }}', '{{ $estudiante->apellidos }}', '{{ $estudiante->cedula_identidad }}', '{{ $estudiante->carrera ?? 'No disponible' }}', '{{ $estudiante->semestre ?? 'No disponible' }}', {{ $estudiante->id }}, 'deshabilitar')">
                      <i class="fas fa-eye mr-2"></i>Detalles
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-blue-800 py-4">No hay estudiantes habilitados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Tabla de estudiantes deshabilitados -->
      <div id="tabla-deshabilitados" class="tab-content bg-white p-4 rounded-lg shadow-md overflow-x-auto hidden">
        <h3 class="text-lg font-semibold text-center text-red-500 mb-3">
          <i class="fas fa-user-times mr-2"></i>Estudiantes Deshabilitados
        </h3>
        <table class="w-full table-auto border-collapse">
          <thead>
            <tr class="bg-blue-500 text-white">
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Nombres</th>
              <th class="px-4 py-2 text-left">Apellidos</th>
              <th class="px-4 py-2 text-left">Cédula de Identidad</th>
              <th class="px-4 py-2 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($estudiantesDeshabilitados as $estudiante)
              <tr class="bg-blue-100 border-b border-red-300 hover:bg-red-200">
                <td class="px-4 py-2">{{ $estudiante->id }}</td>
                <td class="px-4 py-2">{{ $estudiante->nombres }}</td>
                <td class="px-4 py-2">{{ $estudiante->apellidos }}</td>
                <td class="px-4 py-2">{{ $estudiante->cedula_identidad }}</td>
                <td class="px-4 py-2 text-center">
                  <div class="flex justify-center space-x-2">
                    <button 
                      class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition transform hover:scale-105 duration-300"
                      onclick="mostrarModalDetalles('{{ $estudiante->nombres }}', '{{ $estudiante->apellidos }}', '{{ $estudiante->cedula_identidad }}', '{{ $estudiante->carrera ?? 'No disponible' }}', '{{ $estudiante->semestre ?? 'No disponible' }}', {{ $estudiante->id }}, 'habilitar')">
                      <i class="fas fa-eye mr-2"></i>Detalles
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-red-800 py-4">No hay estudiantes deshabilitados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Detalles -->
  <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-md shadow-md p-6 w-full max-w-lg">
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
            <span id="modal-apellidos" class="ml-2"></span>
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

      <div class="mt-6 flex justify-center space-x-4">
        <button 
          id="action-button"
          class="px-4 py-2 rounded-md transition transform hover:scale-105 duration-300"
          onclick="mostrarModalRazon()">
          <i id="action-icon" class="mr-2"></i>
          <span id="action-text"></span>
        </button>
        <button 
          class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition transform hover:scale-105 duration-300"
          onclick="cerrarModal()">
          <i class="fas fa-times mr-2"></i> Cerrar
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Razón -->
  <div id="modal-razon" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-md shadow-md p-6 w-full max-w-md">
      <h2 id="modal-razon-titulo" class="text-xl font-bold mb-4 flex justify-center items-center">
        <i id="modal-razon-icon" class="mr-2"></i>
        <span id="modal-razon-text"></span>
      </h2>
      
      <form id="action-form" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
          <label for="razon" class="block text-sm font-medium text-gray-700 mb-2">
            <span id="label-razon"></span>
          </label>
          <textarea 
            id="razon" 
            name="razon" 
            rows="4" 
            class="w-full border-2 border-gray-300 rounded-lg p-3 focus:outline-none focus:border-blue-500 transition duration-300 ease-in-out"
            placeholder="Ingrese la razón..."
            required></textarea>
        </div>

        <div class="flex justify-center space-x-4">
          <button 
            type="submit" 
            id="confirm-button"
            class="px-4 py-2 rounded-md transition transform hover:scale-105 duration-300">
            <i id="confirm-icon" class="mr-2"></i>
            <span id="confirm-text"></span>
          </button>
          <button 
            type="button"
            class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition transform hover:scale-105 duration-300"
            onclick="cerrarModalRazon()">
            <i class="fas fa-times mr-2"></i> Cancelar
          </button>
        </div>
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
    let timeoutDuration = 2 * 60 * 1000; // 2 min
    let warningDuration = 1 * 60 * 1000; // 1 min
    let warningTimer, logoutTimer;
    let currentStudentId = null;
    let currentAction = null;

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

    // Funcionalidad de pestañas
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                // Remover clases activas
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'text-blue-600', 'bg-blue-50', 'border-blue-600');
                    btn.classList.add('text-gray-500');
                });
                
                // Agregar clases activas al botón seleccionado
                this.classList.add('active', 'text-blue-600', 'bg-blue-50', 'border-blue-600');
                this.classList.remove('text-gray-500');
                
                // Mostrar/ocultar contenido
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                if (tabName === 'habilitados') {
                    document.getElementById('tabla-habilitados').classList.remove('hidden');
                } else {
                    document.getElementById('tabla-deshabilitados').classList.remove('hidden');
                }
            });
        });
    });

    function mostrarModalDetalles(nombres, apellidos, cedula_identidad, carrera, semestre, id, action) {
        document.getElementById('modal-cedula').innerText = cedula_identidad;
        document.getElementById('modal-nombres').innerText = nombres;
        document.getElementById('modal-apellidos').innerText = apellidos;
        document.getElementById('modal-carrera').innerText = carrera;
        document.getElementById('modal-semestre').innerText = semestre;
        
        currentStudentId = id;
        currentAction = action;
        
        const actionButton = document.getElementById('action-button');
        const actionIcon = document.getElementById('action-icon');
        const actionText = document.getElementById('action-text');
        
        if (action === 'deshabilitar') {
            actionButton.className = 'bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition transform hover:scale-105 duration-300';
            actionIcon.className = 'fas fa-user-times mr-2';
            actionText.textContent = 'Deshabilitar estudiante';
        } else {
            actionButton.className = 'bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition transform hover:scale-105 duration-300';
            actionIcon.className = 'fas fa-user-check mr-2';
            actionText.textContent = 'Habilitar estudiante';
        }
        
        document.getElementById('modal').classList.remove('hidden');
    }

    function mostrarModalRazon() {
        const modalRazonTitulo = document.getElementById('modal-razon-titulo');
        const modalRazonIcon = document.getElementById('modal-razon-icon');
        const modalRazonText = document.getElementById('modal-razon-text');
        const labelRazon = document.getElementById('label-razon');
        const confirmButton = document.getElementById('confirm-button');
        const confirmIcon = document.getElementById('confirm-icon');
        const confirmText = document.getElementById('confirm-text');
        const actionForm = document.getElementById('action-form');
        
        if (currentAction === 'deshabilitar') {
            modalRazonTitulo.className = 'text-xl font-bold mb-4 flex justify-center items-center text-yellow-600';
            modalRazonIcon.className = 'fas fa-user-times mr-2';
            modalRazonText.textContent = 'Deshabilitar Estudiante';
            labelRazon.textContent = 'Razón de la deshabilitación:';
            confirmButton.className = 'bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition transform hover:scale-105 duration-300';
            confirmIcon.className = 'fas fa-user-times mr-2';
            confirmText.textContent = 'Deshabilitar';
            actionForm.action = `/admin/gestion-estudiante/deshabilitar-estudiante/${currentStudentId}`;
        } else {
            modalRazonTitulo.className = 'text-xl font-bold mb-4 flex justify-center items-center text-green-600';
            modalRazonIcon.className = 'fas fa-user-check mr-2';
            modalRazonText.textContent = 'Habilitar Estudiante';
            labelRazon.textContent = 'Razón de la habilitación:';
            confirmButton.className = 'bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition transform hover:scale-105 duration-300';
            confirmIcon.className = 'fas fa-user-check mr-2';
            confirmText.textContent = 'Habilitar';
            actionForm.action = `/admin/gestion-estudiante/habilitar-estudiante/${currentStudentId}`;
        }
        
        document.getElementById('razon').value = '';
        document.getElementById('modal').classList.add('hidden');
        document.getElementById('modal-razon').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modal').classList.add('hidden');
    }

    function cerrarModalRazon() {
        document.getElementById('modal-razon').classList.add('hidden');
    }

    // Filtro de búsqueda 
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const activeTab = document.querySelector('.tab-button.active').getAttribute('data-tab');
            const tableId = activeTab === 'habilitados' ? 'tabla-habilitados' : 'tabla-deshabilitados';
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    const id = cells[0].textContent.toLowerCase();
                    const nombres = cells[1].textContent.toLowerCase();
                    const apellidos = cells[2].textContent.toLowerCase();
                    const cedula = cells[3].textContent.toLowerCase();
                    
                    if (id.includes(searchText) || nombres.includes(searchText) || 
                        apellidos.includes(searchText) || cedula.includes(searchText)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    });

    // Inicia los temporizadores al cargar la página
    window.addEventListener('DOMContentLoaded', () => {
        startTimers();
    });

    // Resetea los temporizadores con actividad del usuario
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
        window.addEventListener(evt, () => {
            resetTimers();
            closeModal();
        });
    });
  </script>
</body>
</html>