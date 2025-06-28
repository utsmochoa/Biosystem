<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BioSystem | Gestión de Deudas 💰</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>

<body class="bg-blue-200 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header con efecto especial -->
        <div class="bg-white shadow-xl rounded-lg p-6 mb-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-green-400 to-yellow-400"></div>
            
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-money-bill-wave text-green-500 mr-3"></i>
                        Gestión de Deudas
                        <span class="text-2xl ml-2">🤫</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Panel secreto para administrar las deudas de estudiantes</p>
                </div>
                <a href="{{ route('Gestion.index') }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <!-- Mensajes de éxito/error -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 shadow">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 shadow">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Total Estudiantes</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $estudiantes->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Con Deudas</p>
                        <p class="text-2xl font-bold text-red-600">{{ $estudiantes->where('deuda', '>', 0)->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Deuda Total</p>
                        <p class="text-2xl font-bold text-green-600">Bs. {{ number_format($estudiantes->sum('deuda'), 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de estudiantes -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                <h2 class="text-white text-xl font-semibold flex items-center">
                    <i class="fas fa-table mr-2"></i>
                    Lista de Estudiantes y Deudas
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estudiante
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cédula
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Carrera
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Deuda Actual
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($estudiantes as $estudiante)
                            <tr class="hover:bg-gray-50 transition-colors {{ $estudiante->deuda > 0 ? 'border-l-4 border-red-400' : 'border-l-4 border-green-400' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 rounded-full p-2 mr-3">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $estudiante->nombres }} {{ $estudiante->apellidos }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $estudiante->cedula_identidad }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $estudiante->carrera }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        {{ $estudiante->deuda > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        <i class="fas fa-dollar-sign mr-1"></i>
                                        {{ number_format($estudiante->deuda, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openDebtModal({{ $estudiante->id }}, '{{ $estudiante->nombres }} {{ $estudiante->apellidos }}', {{ $estudiante->deuda }})"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition transform hover:scale-105 shadow-md">
                                        <i class="fas fa-edit mr-1"></i> Modificar Deuda
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-users-slash text-4xl mb-2"></i>
                                    <p>No hay estudiantes registrados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para editar deuda -->
    <div id="debtModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-6 m-4 max-w-md w-full transform transition-all">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                    Modificar Deuda
                </h3>
                <button onclick="closeDebtModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="debtForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Estudiante:
                    </label>
                    <p id="studentName" class="text-lg font-semibold text-blue-600"></p>
                </div>
                
                <div class="mb-6">
                    <label for="deuda" class="block text-sm font-medium text-gray-700 mb-2">
                        Nueva Deuda ($):
                    </label>
                    <input type="number" 
                           name="deuda" 
                           id="deuda" 
                           step="0.01" 
                           min="0" 
                           max="99999.99"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeDebtModal()"
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition transform hover:scale-105">
                        <i class="fas fa-save mr-1"></i> Guardar
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
        
        function openDebtModal(id, name, currentDebt) {
            document.getElementById('studentName').textContent = name;
            document.getElementById('deuda').value = currentDebt;
            document.getElementById('debtForm').action = `/admin/estudiantes/${id}/deuda`;
            document.getElementById('debtModal').classList.remove('hidden');
            document.getElementById('debtModal').classList.add('flex');
        }
        
        function closeDebtModal() {
            document.getElementById('debtModal').classList.add('hidden');
            document.getElementById('debtModal').classList.remove('flex');
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('debtModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDebtModal();
            }
        });
        
        // Efectos especiales al cargar
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada para las filas
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(20px)';
                    row.style.transition = 'all 0.3s ease';
                    setTimeout(() => {
                        row.style.opacity = '1';
                        row.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });
        });
    </script>
</body>
</html>