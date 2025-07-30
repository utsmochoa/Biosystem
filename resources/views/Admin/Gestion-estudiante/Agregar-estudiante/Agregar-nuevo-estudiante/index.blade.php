<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Estudiante Nuevo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>
<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-12">
            <h1 class="text-xl font-bold text-blue-700 pr-24">Añadir estudiante nuevo</h1>
        </div>
        <p class="text-gray-600 text-sm mb-6">Llene el formulario con los datos del estudiante y luego presione siguiente</p>
        
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
    
        <form action="/admin/gestion-estudiante/añadir-nuevo-estudiante/pedir-huella" method="post" enctype="multipart/form-data" id="studentForm">
            @csrf
            <div class="mb-4">
                <label for="foto" class="block text-sm font-semibold text-gray-700">Foto:</label>
                <div class="flex items-center space-x-2">
                    <input type="file" id="foto" name="foto" accept="image/*" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <button type="button" onclick="openCamera()" class="text-blue-600 hover:text-blue-800" title="Tomar foto con cámara">
                        📷
                    </button>
                </div>
                <video id="video" class="mt-2 w-full rounded hidden" autoplay></video>
                <canvas id="canvas" class="hidden"></canvas>
            </div>
            <div class="mb-4">
                <label for="nombres" class="block text-sm font-semibold text-gray-700">Nombres:</label>
                <input type="text" id="nombres" name="nombres" maxlength="32" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div>
            <div class="mb-4">
                <label for="apellidos" class="block text-sm font-semibold text-gray-700">Apellidos:</label>
                <input type="text" id="apellidos" name="apellidos" maxlength="32" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div>
            <div class="mb-4">
                <label for="cedula" class="block text-sm font-semibold text-gray-700">Cédula de identidad:</label>
                <input type="text" id="cedula" name="cedula_identidad" maxlength="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div>
            <div class="mb-4">
                <label for="carrera" class="block text-sm font-semibold text-gray-700">Carrera:</label>
                <select id="carrera" name="carrera" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <option value="">Seleccione una carrera</option>
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
            <div class="mb-4">
                <label for="semestre" class="block text-sm font-semibold text-gray-700">Semestre:</label>
                <select id="semestre" name="semestre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <option value="">Seleccione una carrera</option>
                    <option value="1er semestre">1er semestre</option>
                    <option value="2do semestre">2do semestre</option>
                    <option value="3er semestre">3er semestre</option>
                    <option value="4to semestre">4to semestre</option>
                    <option value="5to semestre">5to semestre</option>
                    <option value="6to semestre">6to semestre</option>
                </select>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="{{ route('Gestion.seleccion')}}" 
                   class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                    Cancelar
                </a>
                <div class="flex space-x-4">
                    <button type="reset" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                        Vaciar
                    </button>
                    <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                        Siguiente
                    </button>
                </div>
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
        let isSubmitting = false;
    
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

        document.addEventListener('DOMContentLoaded', () => {
            const nombresInput = document.getElementById('nombres');
            const apellidosInput = document.getElementById('apellidos');
            const cedulaInput = document.getElementById('cedula');
            const form = document.getElementById('studentForm');
            const submitBtn = document.getElementById('submitBtn');

            // Solo letras en nombres y apellidos
            function soloLetras(e) {
                const valor = e.target.value;
                e.target.value = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            }

            nombresInput.addEventListener('input', soloLetras);
            apellidosInput.addEventListener('input', soloLetras);

            // Solo números en cédula mientras escribe
            cedulaInput.addEventListener('input', () => {
                cedulaInput.value = cedulaInput.value.replace(/\D/g, '');
            });

            // Manejar el envío del formulario
            form.addEventListener('submit', handleFormSubmit);

            // Manejar el evento "Enter" en todos los campos de entrada
            const inputFields = form.querySelectorAll('input, select');
            inputFields.forEach(input => {
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault(); // Prevenir el comportamiento por defecto
                        handleFormSubmit(e); // Manejar el envío
                    }
                });
            });

            function handleFormSubmit(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return;
                }
                
                isSubmitting = true;
                disableSubmitButton();

                // Formatear la cédula antes de enviar
                let cedula = cedulaInput.value;
                if (cedula) {
                    let cedulaFormateada = new Intl.NumberFormat('es-VE').format(parseInt(cedula));
                    cedulaInput.value = cedulaFormateada;
                }

                // Forzar el envío del formulario si fue llamado desde un keydown
                if (e.type !== 'submit') {
                    form.requestSubmit();
                }
            }

            function disableSubmitButton() {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-blue-600', 'hover:scale-105');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            }
        });

        let video = null;
        let canvas = null;
        let stream = null;
    
        function openCamera() {
            video = document.getElementById('video');
            canvas = document.getElementById('canvas');
    
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(mediaStream) {
                    stream = mediaStream;
                    video.srcObject = stream;
                    video.classList.remove('hidden');
    
                    // Agrega el botón de captura si no existe aún
                    if (!document.getElementById('captureBtn')) {
                        const captureBtn = document.createElement('button');
                        captureBtn.textContent = 'Tomar Foto';
                        captureBtn.id = 'captureBtn';
                        captureBtn.type = 'button';
                        captureBtn.className = 'mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full';
                        captureBtn.onclick = function () {
                            takePhoto();
                            stopCamera();
                        };
                        video.parentNode.appendChild(captureBtn);
                    }
                })
                .catch(function(err) {
                    alert("No se pudo acceder a la cámara: " + err);
                });
        }
    
        function takePhoto() {
            const width = video.videoWidth;
            const height = video.videoHeight;
    
            canvas.width = width;
            canvas.height = height;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, width, height);
    
            canvas.toBlob(function(blob) {
                const fileInput = document.getElementById('foto');
                const file = new File([blob], "captura.jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
    
                video.classList.add('hidden');
                const captureBtn = document.getElementById('captureBtn');
                if (captureBtn) captureBtn.remove();
            }, 'image/jpeg');
        }
    
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        // Oculta el mensaje de error tras 5 segundos
        setTimeout(() => {
            const alert = document.querySelector('.bg-red-50.border-red-200');
            if (alert) {
                alert.classList.add('opacity-0', 'transition-opacity', 'duration-1000');
                setTimeout(() => alert.remove(), 1000);
            }
        }, 5000);
    </script>

<script>
let yaEnviado = false;

function bloquearEnvio() {
    if (yaEnviado) return false;

    yaEnviado = true;
    const btn = document.getElementById('btn-siguiente');
    btn.disabled = true;
    btn.innerText = 'Procesando...';

    return true;
}
</script>

</body>
</html>