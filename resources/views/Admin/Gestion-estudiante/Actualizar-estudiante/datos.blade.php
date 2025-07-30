<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Estudiante</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>
<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-12">
            <h1 class="text-xl font-bold text-blue-700 pr-24">Actualizar Estudiante</h1>
        </div>
        <p class="text-gray-600 text-sm mb-6">Seleccione los datos que desea actualizar y complete el formulario</p>
        
        @if (session('error'))
            <div class="flex items-start space-x-3 bg-red-50 border border-red-200 rounded-xl p-4 shadow-md mb-6">
                <div class="flex-shrink-0">
                    <i class="fas fa-circle-exclamation text-red-500 text-2xl"></i>
                </div>
                <div class="flex-1 text-sm text-red-800">
                    <p class="font-bold text-base mb-1">¡Error!</p>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.estudiantes.update', $id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $id }}">
            
            <div class="mb-4">
                <div class="flex items-center">
                    <input type="checkbox" id="update-nombres" name="update_fields[]" value="nombres" class="mr-2 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="update-nombres" class="block text-sm font-semibold text-gray-700">Nombres:</label>
                </div>
                <input type="text" id="nombres" name="nombres" value="{{ $nombres }}" maxlength="32"
                    class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                    disabled>
            </div>

            <div class="mb-4">
                <div class="flex items-center">
                    <input type="checkbox" id="update-apellidos" name="update_fields[]" value="apellidos" class="mr-2 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="update-apellidos" class="block text-sm font-semibold text-gray-700">Apellidos:</label>
                </div>
                <input type="text" id="apellidos" name="apellidos" value="{{ $apellidos }}" maxlength="32"
                    class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                    disabled>
            </div>

            <div class="mb-4">
                <div class="flex items-center">
                    <input type="checkbox" id="update-cedula" name="update_fields[]" value="cedula_identidad" class="mr-2 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="update-cedula" class="block text-sm font-semibold text-gray-700">Cédula de identidad:</label>
                </div>
                <input type="text" id="cedula" name="cedula_identidad" value="{{ $cedula }}" maxlength="8"
                    class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                    disabled>
            </div>

            <div class="mb-4">
                <div class="flex items-center">
                    <input type="checkbox" id="update-carrera" name="update_fields[]" value="carrera" class="mr-2 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="update-carrera" class="block text-sm font-semibold text-gray-700">Carrera:</label>
                </div>
                <select id="carrera" name="carrera"
                    class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                    disabled>
                    <option value="{{ $carrera }}" selected>{{ $carrera }}</option>
                    <optgroup label="Administración">
                        <option value="Ciencias comercial">Mención: Ciencias Comercial</option>
                        <option value="Costos">Mención: Costos</option>
                        <option value="Mercadotecnia">Mención: Mercadotecnia</option>
                    </optgroup>
                    <option value="Publicidad">Publicidad</option>
                    <option value="Relaciones industriales">Relaciones Industriales</option>
                    <option value="Riesgos y seguros">Riesgos y Seguros</option>
                    <option value="Secretaría">Secretaría</option>
                    <optgroup label="Turismo">
                        <option value="Servicios turisticos">Mención: Servicios Turísticos</option>
                        <option value="Hoteleria">Mención: Hoteleria</option>
                    </optgroup>
                    <optgroup label="Tecnológicas">
                        <option value="Diseno grafico">Diseño Gráfico</option>
                        <option value="Diseno obras civiles">Diseño de Obras Civiles</option>
                        <option value="Diseno industrial">Diseño Industrial</option>
                        <option value="Electronica">Electrónica</option>
                        <option value="Informatica">Informática</option>
                        <option value="Seguridad industrial">Seguridad Industrial</option>
                        <option value="Construccion civil">Tecnología de la Construcción Civil</option>
                        <optgroup label="Electricidad">
                            <option value="Instalaciones electricas">Mención: Instalaciones electricas</option>
                            <option value="Electricidad mantenimiento">Mención: Mantenimiento</option>
                        </optgroup>
                        <optgroup label="Tecnología Mecánica">
                            <option value="Mecanica fabricacion">Mención: Fabricacion</option>
                            <option value="Mecanica mantenimiento">Mención: Mantenimiento</option>
                        </optgroup>
                    </optgroup>
                </select>
            </div>

            <div class="mb-6">
                <div class="flex items-center">
                    <input type="checkbox" id="update-semestre" name="update_fields[]" value="semestre" class="mr-2 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="update-semestre" class="block text-sm font-semibold text-gray-700">Semestre:</label>
                </div>
                <select id="semestre" name="semestre"
                    class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                    disabled>
                    <option value="{{ $semestre }}" selected>{{ $semestre }}</option>
                    <option value="1er semestre">1er semestre</option>
                    <option value="2do semestre">2do semestre</option>
                    <option value="3er semestre">3er semestre</option>
                    <option value="4to semestre">4to semestre</option>
                    <option value="5to semestre">5to semestre</option>
                    <option value="6to semestre">6to semestre</option>
                </select>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="{{ route('Gestion.actualizar') }}" 
                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                    Cancelar
                </a>
                <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                    Actualizar
                </button>
            </div>
        </form>
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
        
        // Obtener todos los checkboxes y campos de texto
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const inputId = checkbox.id.replace('update-', '');
                const inputField = document.getElementById(inputId);
                if (checkbox.checked) {
                    inputField.disabled = false;
                    inputField.classList.add('bg-white'); // Cambia a fondo blanco cuando está habilitado
                    inputField.classList.remove('bg-blue-50');
                } else {
                    inputField.disabled = true;
                    inputField.classList.remove('bg-white');
                    inputField.classList.add('bg-blue-50'); // Vuelve al fondo azul claro cuando está deshabilitado
                }
            });
        });

        // Validaciones del formulario
        document.addEventListener('DOMContentLoaded', () => {
            const nombresInput = document.getElementById('nombres');
            const apellidosInput = document.getElementById('apellidos');
            const cedulaInput = document.getElementById('cedula');

            // Solo letras en nombres y apellidos
            function soloLetras(e) {
                const valor = e.target.value;
                e.target.value = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            }

            if(nombresInput) nombresInput.addEventListener('input', soloLetras);
            if(apellidosInput) apellidosInput.addEventListener('input', soloLetras);

            // Solo números en cédula mientras escribe
            if(cedulaInput) {
                cedulaInput.addEventListener('input', () => {
                    cedulaInput.value = cedulaInput.value.replace(/\D/g, '');
                });
            }

            // Formatear la cédula al enviar el formulario
            const form = document.querySelector('form');
            if(form) {
                form.addEventListener('submit', () => {
                    if(cedulaInput && !cedulaInput.disabled) {
                        let cedula = cedulaInput.value;
                        if (!cedula) return;

                        // Insertar puntos como separadores de miles (e.g., 1234567 → 1.234.567)
                        let cedulaFormateada = new Intl.NumberFormat('es-VE').format(parseInt(cedula));
                        cedulaInput.value = cedulaFormateada;
                    }
                });
            }
        });

        // Oculta el mensaje de error tras 5 segundos
        setTimeout(() => {
            const alert = document.querySelector('.bg-red-50.border-red-200');
            if (alert) {
                alert.classList.add('opacity-0', 'transition-opacity', 'duration-1000');
                setTimeout(() => alert.remove(), 1000);
            }
        }, 5000);
    </script>
</body>
</html>