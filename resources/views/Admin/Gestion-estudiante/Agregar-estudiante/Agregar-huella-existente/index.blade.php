<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Añadir Huella</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>
<style>
  .btn-disabled {
    opacity: 0.7;
    cursor: not-allowed;
    background-color: #ccc !important;
  }
  .hidden {
    display: none;
  }
</style>
<body class="min-h-screen bg-blue-200 flex justify-center items-center">
  <div class="bg-white shadow-md rounded-md p-6 w-full max-w-4xl">
    <div class="relative mb-2">
      <a href="{{ route('Gestion.seleccion') }}"
         class="absolute left-0 top-1/2 transform -translate-y-1/2 text-blue-700 hover:text-blue-900 font-medium inline-flex items-center transition">
          <i class="fas fa-arrow-left mr-2"></i> Volver
      </a>
    
      <div class="flex flex-col items-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-1">
        <h1 class="text-2xl font-bold text-center text-blue-700">Añadir huella a estudiante existente</h1>
      </div>
    </div>

    <!-- Alertas -->
    <div id="alert-container" class="mb-4 hidden">
      <div id="alert-message" class="p-4 rounded-md border-l-4 mb-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i id="alert-icon" class="text-lg"></i>
          </div>
          <div class="ml-3">
            <p id="alert-text" class="text-sm font-medium"></p>
          </div>
        </div>
      </div>
    </div>

    @if($estudiantes->isEmpty())
      <div class="p-6 text-center text-blue-800 font-semibold">
        <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
        <p class="text-lg">Todos los estudiantes tienen huella registrada.</p>
      </div>
    @else
    <div class="p-4">
      <div class="relative mb-4">
        <input type="text" id="searchInput" placeholder="Buscar estudiante..." class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 absolute left-3 top-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

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
            @foreach($estudiantes as $index => $estudiante)
              <tr class="bg-blue-100 border-b border-blue-200 hover:bg-blue-200 
                  {{ $loop->last ? 'rounded-b-lg' : '' }}" data-estudiante-id="{{ $estudiante->id }}">
                <td class="px-4 py-2">{{ $estudiante->id }}</td>
                <td class="px-4 py-2">{{ $estudiante->nombres }}</td>
                <td class="px-4 py-2">{{ $estudiante->apellidos }}</td>
                <td class="px-4 py-2">{{ $estudiante->cedula_identidad }}</td>
                <td class="px-4 py-2 text-center">
                  <button 
                    class="bg-blue-600 text-white px-2 py-1 rounded-md hover:bg-blue-700 transition duration-300 transform hover:scale-105 mr-2"
                    onclick="mostrarModal('{{ $estudiante->nombres }}', '{{ $estudiante->apellidos }}', '{{ $estudiante->cedula_identidad }}', '{{ $estudiante->carrera ?? 'No disponible' }}', '{{ $estudiante->semestre ?? 'No disponible' }}', {{ $estudiante->id }})">
                    <i class="fas fa-eye mr-1"></i>Detalles
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif
  </div>

  <!-- Modal Detalles -->
  <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-100 rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 transform transition-all">
      <h2 class="text-xl font-bold text-blue-700 mb-6 flex justify-center items-center">
        <i class="fas fa-user-graduate mr-2"></i>
        Detalles del Estudiante
      </h2>
      
      <div class="space-y-3 mb-6">
        <div class="flex items-center">
          <i class="fas fa-id-card w-5 text-gray-500 mr-2"></i>
          <span class="font-semibold">Cédula:</span>
          <span id="modal-cedula" class="ml-2"></span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-user w-5 text-gray-500 mr-2"></i>
          <span class="font-semibold">Nombres:</span>
          <span id="modal-nombres" class="ml-2"></span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-user w-5 text-gray-500 mr-2"></i>
          <span class="font-semibold">Apellidos:</span>
          <span id="modal-apellidos" class="ml-2"></span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-graduation-cap w-5 text-gray-500 mr-2"></i>
          <span class="font-semibold">Carrera:</span>
          <span id="modal-carrera" class="ml-2"></span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-calendar-alt w-5 text-gray-500 mr-2"></i>
          <span class="font-semibold">Semestre:</span>
          <span id="modal-semestre" class="ml-2"></span>
        </div>
      </div>

      <div class="mt-6 flex justify-center">
        <button 
          class="bg-red-500 text-white px-8 py-2 rounded-md hover:bg-red-600 mr-2 transition duration-300 transform hover:scale-105"
          onclick="cerrarModal()">
          <i class="fas fa-times mr-2"></i> Cerrar
        </button>
        <button 
          id="btn-agregar-huella"
          class="ml-2 bg-green-500 text-white px-2 py-2 rounded-md hover:bg-green-600 transition-colors duration-300 transform hover:scale-105 flex items-center"
          onclick="iniciarCapturaHuella(currentEstudianteId)">
          <i class="fas fa-fingerprint mr-2"></i>
          <span id="btn-text">Añadir huella</span>
          <span id="btn-loading" class="hidden ml-2">
            <i class="fas fa-spinner fa-spin"></i>
          </span>
        </button>
      </div>
    </div>
  </div>

  <!-- Modal de Captura de Huella -->
  <div id="modal-captura" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4 transform transition-all">
      <h2 class="text-xl font-bold text-blue-700 mb-6 text-center">
        <i class="fas fa-fingerprint mr-2"></i>
        Captura de Huella Digital
      </h2>
      
      <div class="text-center mb-6">
        <div class="text-6xl mb-4" id="icono-captura">👆</div>
        <p id="mensaje-captura" class="text-gray-600 mb-4">Iniciando proceso de captura...</p>
        <div id="spinner" class="hidden">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
        </div>
      </div>

      <div class="flex justify-center">
        <button 
          id="btn-cancelar-captura"
          class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 transition duration-300"
          onclick="cancelarCaptura()">
          <i class="fas fa-times mr-2"></i> Cancelar
        </button>
      </div>
    </div>
  </div>

  <!-- Formulario de logout -->
  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="inactive" id="inactive" value="0">
  </form>

  <!-- Modal de inactividad -->
  <div id="inactivity-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full text-center">
      <h2 class="text-xl font-bold text-gray-800 mb-2">⚠ Inactividad detectada</h2>
      <p class="text-gray-600 mb-4">Tu sesión se cerrará automáticamente en 1 minuto.</p>
      <button onclick="closeInactivityModal()" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
        Entendido
      </button>
    </div>
  </div>

  <script>
    let currentEstudianteId = null;
    let capturaEnProceso = false;
    const baseCapturarUrl = "{{ route('gestion.agregar-huella.capturar', ['id' => '__id__']) }}";


    // Configuración de inactividad
    let timeoutDuration = 2 * 60 * 1000; // 2 min
    let warningDuration = 1 * 60 * 1000; // 1 min
    let warningTimer, logoutTimer;

    // Configurar CSRF token para AJAX
    document.addEventListener('DOMContentLoaded', function() {
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
      // Configurar AJAX para incluir CSRF token si axios está disponible
      if (typeof axios !== 'undefined') {
        window.axios = axios.create({
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
      }
    });

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

    function closeInactivityModal() {
      document.getElementById('inactivity-modal').classList.add('hidden');
    }

    // Inicializar temporizadores
    window.addEventListener('DOMContentLoaded', () => {
      startTimers();
    });

    // Resetear temporizadores con actividad del usuario
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
      window.addEventListener(evt, () => {
        resetTimers();
        closeInactivityModal();
      });
    });

    function mostrarModal(nombres, apellidos, cedula_identidad, carrera, semestre, id) {
      currentEstudianteId = id;
      
      document.getElementById('modal-cedula').innerText = cedula_identidad;
      document.getElementById('modal-nombres').innerText = nombres;
      document.getElementById('modal-apellidos').innerText = apellidos;
      document.getElementById('modal-carrera').innerText = carrera;
      document.getElementById('modal-semestre').innerText = semestre;
      document.getElementById('modal').classList.remove('hidden');
    }

    function cerrarModal() {
      document.getElementById('modal').classList.add('hidden');
      currentEstudianteId = null;
    }

    function cerrarModalCaptura() {
      document.getElementById('modal-captura').classList.add('hidden');
      capturaEnProceso = false;
    }

    function cancelarCaptura() {
      if (capturaEnProceso) {
        // Cancelar la captura en proceso
        capturaEnProceso = false;
        mostrarAlerta('warning', 'Captura de huella cancelada');
        
        // Reactivar el botón
        const btn = document.getElementById('btn-agregar-huella');
        btn.classList.remove('btn-disabled');
        btn.disabled = false;
        document.getElementById('btn-text').classList.remove('hidden');
        document.getElementById('btn-loading').classList.add('hidden');
      }
      cerrarModalCaptura();
    }

    function iniciarCapturaHuella(estudianteId) {
      if (!estudianteId) {
        mostrarAlerta('error', 'No se ha seleccionado un estudiante');
        return;
      }

      if (capturaEnProceso) {
        mostrarAlerta('warning', 'Ya hay una captura en proceso');
        return;
      }

      // Desactivar el botón
      const btn = document.getElementById('btn-agregar-huella');
      btn.classList.add('btn-disabled');
      btn.disabled = true;
      document.getElementById('btn-text').classList.add('hidden');
      document.getElementById('btn-loading').classList.remove('hidden');

      // Cerrar modal de detalles y abrir modal de captura
      cerrarModal();
      document.getElementById('modal-captura').classList.remove('hidden');
      
      // Resetear estado del modal de captura
      document.getElementById('icono-captura').textContent = '👆';
      document.getElementById('mensaje-captura').textContent = 'Iniciando proceso de captura...';
      document.getElementById('spinner').classList.remove('hidden');
      document.getElementById('btn-cancelar-captura').disabled = false;
      document.getElementById('btn-cancelar-captura').innerHTML = '<i class="fas fa-times mr-2"></i> Cancelar';
      
      capturaEnProceso = true;
      currentEstudianteId = estudianteId;

      const url = baseCapturarUrl.replace('__id__', estudianteId);

      // Realizar petición AJAX para capturar huella
      fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        capturaEnProceso = false;
        document.getElementById('spinner').classList.add('hidden');
        
        if (data.success) {
          // Éxito en la captura
          document.getElementById('icono-captura').textContent = '✅';
          document.getElementById('mensaje-captura').textContent = data.message;
          document.getElementById('btn-cancelar-captura').innerHTML = '<i class="fas fa-check mr-2"></i> Cerrar';
          
          mostrarAlerta('success', data.message);
          
          // Remover fila de la tabla
          setTimeout(() => {
            const fila = document.querySelector(`tr[data-estudiante-id="${currentEstudianteId}"]`);
            if (fila) {
              fila.remove();
            }
            cerrarModalCaptura();
            
            // Verificar si ya no hay estudiantes
            const filasRestantes = document.querySelectorAll('tbody tr').length;
            if (filasRestantes === 0) {
              location.reload();
            }
          }, 3000);
          
        } else {
          // Error en la captura
          document.getElementById('icono-captura').textContent = '❌';
          document.getElementById('mensaje-captura').textContent = data.message || 'Error en la captura de huella';
          document.getElementById('btn-cancelar-captura').innerHTML = '<i class="fas fa-times mr-2"></i> Cerrar';
          
          mostrarAlerta('error', data.message || 'Error en la captura de huella');
        }
      })
      .catch(error => {
        capturaEnProceso = false;
        document.getElementById('spinner').classList.add('hidden');
        document.getElementById('icono-captura').textContent = '❌';
        document.getElementById('mensaje-captura').textContent = 'Error de conexión';
        document.getElementById('btn-cancelar-captura').innerHTML = '<i class="fas fa-times mr-2"></i> Cerrar';
        
        mostrarAlerta('error', 'Error de conexión al capturar la huella');
        console.error('Error:', error);
      })
      .finally(() => {
        // Reactivar el botón solo si la captura fue cancelada
        if (!capturaEnProceso) {
          const btn = document.getElementById('btn-agregar-huella');
          btn.classList.remove('btn-disabled');
          btn.disabled = false;
          document.getElementById('btn-text').classList.remove('hidden');
          document.getElementById('btn-loading').classList.add('hidden');
        }
      });
    }

    function mostrarAlerta(tipo, mensaje) {
      const alertContainer = document.getElementById('alert-container');
      const alertMessage = document.getElementById('alert-message');
      const alertIcon = document.getElementById('alert-icon');
      const alertText = document.getElementById('alert-text');

      // Configurar colores y iconos según el tipo
      alertMessage.className = 'p-4 rounded-md border-l-4 mb-4';
      
      switch(tipo) {
        case 'success':
          alertMessage.classList.add('bg-green-50', 'border-green-400');
          alertIcon.className = 'fas fa-check-circle text-green-400';
          alertText.className = 'text-sm font-medium text-green-800';
          break;
        case 'error':
          alertMessage.classList.add('bg-red-50', 'border-red-400');
          alertIcon.className = 'fas fa-exclamation-circle text-red-400';
          alertText.className = 'text-sm font-medium text-red-800';
          break;
        case 'warning':
          alertMessage.classList.add('bg-yellow-50', 'border-yellow-400');
          alertIcon.className = 'fas fa-exclamation-triangle text-yellow-400';
          alertText.className = 'text-sm font-medium text-yellow-800';
          break;
        default:
          alertMessage.classList.add('bg-blue-50', 'border-blue-400');
          alertIcon.className = 'fas fa-info-circle text-blue-400';
          alertText.className = 'text-sm font-medium text-blue-800';
      }

      alertText.textContent = mensaje;
      alertContainer.classList.remove('hidden');

      // Auto-ocultar después de 5 segundos
      setTimeout(() => {
        alertContainer.classList.add('hidden');
      }, 5000);
    }

    // Funcionalidad de búsqueda
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Cerrar modales con Escape
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        if (!document.getElementById('modal').classList.contains('hidden')) {
          cerrarModal();
        }
        if (!document.getElementById('modal-captura').classList.contains('hidden')) {
          cancelarCaptura();
        }
      }
    });

    // Cerrar modales al hacer clic fuera
    document.getElementById('modal').addEventListener('click', function(event) {
      if (event.target === this) {
        cerrarModal();
      }
    });

    document.getElementById('modal-captura').addEventListener('click', function(event) {
      if (event.target === this) {
        cancelarCaptura();
      }
    });
  </script>
</body>
</html>