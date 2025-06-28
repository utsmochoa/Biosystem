<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrar Estudiante</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->

</head>
<body class="min-h-screen bg-blue-200 flex justify-center items-center">
  <div class="bg-white shadow-md rounded-md p-6 w-full max-w-4xl">
    <!-- Header -->
    <div class="relative mb-2">
      <a href="{{ route('Gestion.index') }}"
         class="absolute left-0 top-1/2 transform -translate-y-1/2 text-blue-700 hover:text-blue-900 font-medium inline-flex items-center transition">
          <i class="fas fa-arrow-left mr-2"></i> Volver
      </a>
    
      <div class="flex flex-col items-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-1">
        <h1 class="text-2xl font-bold text-center text-blue-700">Deshabilitar/Habilitar estudiantes</h1>
      </div>
    </div>

    <!-- Alerta de éxito -->
    @if(session('success'))
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 mx-4">
        {{ session('success') }}
      </div>
    @endif

    <!-- Buscar -->
    <div class="p-4">
      <div class="relative mb-4">
        <input type="text" id="searchInput" placeholder="Buscar estudiante..." class=" w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500 transition duration-300 ease-in-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 absolute left-3 top-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      
      <!-- Tabla de estudiantes -->
      <div class="bg-white p-4 rounded-lg shadow-md overflow-x-auto">
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
            @forelse($estudiantes as $estudiante)
              <tr class="bg-blue-100 border-b border-blue-200 hover:bg-blue-200">
                <td class="px-4 py-2">{{ $estudiante->id }}</td>
                <td class="px-4 py-2">{{ $estudiante->nombres }}</td>
                <td class="px-4 py-2">{{ $estudiante->apellidos }}</td>
                <td class="px-4 py-2">{{ $estudiante->cedula_identidad }}</td>
                <td class="px-4 py-2 text-center">
                  <div class="flex justify-center space-x-2">
                    <button 
                      class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition transform hover:scale-105 duration-300"
                      onclick="mostrarModal('{{ $estudiante->nombres }}', '{{ $estudiante->apellidos }}', '{{ $estudiante->cedula_identidad }}', '{{ $estudiante->carrera ?? 'No disponible' }}', '{{ $estudiante->semestre ?? 'No disponible' }}', {{ $estudiante->id }})">
                      <i class="fas fa-eye mr-2"></i>Detalles
                    </button>
                     
                    
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-blue-800 py-4">No hay estudiantes registrados.</td>
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
        <form id="delete-form" action="" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition transform hover:scale-105 duration-300">
           <i class="fas fa-circle-minus mr-2"></i>  Deshabilitar estudiante
          </button>
        </form>
        <button 
          class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition transform hover:scale-105 duration-300"
          onclick="cerrarModal()">
          <i class="fas fa-times mr-2"></i> Cerrar
        </button>
      </div>
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

    
    function mostrarModal(nombres, apellidos, cedula_identidad, carrera, semestre, id) {
      document.getElementById('modal-cedula').innerText = cedula_identidad;
      document.getElementById('modal-nombres').innerText = nombres;
      document.getElementById('modal-apellidos').innerText = apellidos;
      document.getElementById('modal-carrera').innerText = carrera;
      document.getElementById('modal-semestre').innerText = semestre;
      
      // Actualizar formulario para borrar
      document.getElementById('delete-form').action = `/admin/gestion-estudiante/eliminar-estudiante/${id}`;

      document.getElementById('modal').classList.remove('hidden');
    }

    function cerrarModal() {
      document.getElementById('modal').classList.add('hidden');
    }

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