<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Estudiante</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-200 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md">
        <!-- Header -->
        <div class="bg-blue-700 rounded-t-lg p-4 flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-10 h-10 mr-2">
            <h1 class="text-white text-lg font-bold">Actualizar Estudiante</h1>
        </div>

        <!-- Form Section -->
        <div class="bg-blue-400 p-6 rounded-b-lg">
            <p class="text-black text-center font-semibold mb-4">
                Seleccione los datos que desea actualizar y complete el formulario
            </p>

            <form action="{{ route('admin.estudiantes.update', $id) }}" method="POST">
                @csrf <!-- Laravel CSRF Token -->
                @method('PUT') <!-- Método HTTP PUT para actualización -->
                
                <input type="hidden" name="id" value="{{ $id }}">
                
                <div class="mb-4">
                    <input type="checkbox" id="update-nombres" name="update_fields[]" value="nombres"
                        class="mr-2">
                    <label for="update-nombres" class="text-black font-semibold">Actualizar Nombres:</label>
                    <input type="text" id="nombres" name="nombres" value="{{ $nombres }}"
                        class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600"
                        disabled>
                </div>

                <div class="mb-4">
                    <input type="checkbox" id="update-apellidos" name="update_fields[]" value="apellidos"
                        class="mr-2">
                    <label for="update-apellidos" class="text-black font-semibold">Actualizar Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" value="{{ $apellidos }}"
                        class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600"
                        disabled>
                </div>

                <div class="mb-4">
                    <input type="checkbox" id="update-cedula" name="update_fields[]" value="cedula_identidad"
                        class="mr-2">
                    <label for="update-cedula" class="text-black font-semibold">Actualizar Cédula de Identidad:</label>
                    <input type="text" id="cedula" name="cedula_identidad" value="{{ $cedula }}"
                        class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600"
                        disabled>
                </div>

                <div class="mb-4">
                    <input type="checkbox" id="update-carrera" name="update_fields[]" value="carrera"
                        class="mr-2">
                    <label for="update-carrera" class="text-black font-semibold">Actualizar Carrera:</label>
                    <input type="text" id="carrera" name="carrera" value="{{ $carrera }}"
                        class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600"
                        disabled>
                </div>

                <div class="mb-6">
                    <input type="checkbox" id="update-semestre" name="update_fields[]" value="semestre"
                        class="mr-2">
                    <label for="update-semestre" class="text-black font-semibold">Actualizar Semestre:</label>
                    <input type="text" id="semestre" name="semestre" value="{{ $semestre }}"
                        class="w-full px-3 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600"
                        disabled>
                </div>

                <!-- Buttons -->
                <div class="flex justify-between">
                    <button type="submit" class="bg-blue-700 text-white font-semibold px-4 py-2 rounded hover:bg-blue-800">Actualizar</button>
                    <a href="{{ route('Gestion.actualizar') }}" class="bg-red-500 text-white font-semibold px-4 py-2 rounded hover:bg-red-600 text-center">Cancelar</a>
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
        
        // Obtener todos los checkboxes y campos de texto
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                // Habilitar o deshabilitar el campo asociado al checkbox
                const inputId = checkbox.id.replace('update-', '');
                const inputField = document.getElementById(inputId);
                if (checkbox.checked) {
                    inputField.disabled = false;
                } else {
                    inputField.disabled = true;
                    // No limpiamos el valor para conservar los datos originales
                }
            });
        });
    </script>
</body>
</html>