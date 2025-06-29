<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Administrador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>

<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-12">
            <h1 class="text-xl font-bold text-blue-700 pr-24">Agregar administrador</h1>
        </div>
        <p class="text-gray-600 text-sm mb-6">Llene el formulario con los datos del administrador y luego presione siguiente</p>

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

        <form action="{{ route('Agregar.admin.pedir-huella') }}" method="post" id="adminForm">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-semibold text-gray-700">Nombre de usuario:</label>
                <input type="text" id="name" name="name" maxlength="15"
                       class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                       required>
                <p class="text-xs text-gray-500 mt-1">Máximo 15 caracteres</p>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-semibold text-gray-700">Contraseña:</label>
                <input type="password" id="password" name="password"
                       class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                       required>
            </div>

            <div class="mb-6">
                <label for="confirm_password" class="block text-sm font-semibold text-gray-700">Confirmar contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       class="mt-1 block w-full rounded-md border-2 border-blue-300 bg-blue-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                       required>
                <div id="passwordError" class="text-red-500 text-xs mt-1 hidden">Las contraseñas no coinciden</div>
            </div>

            <div class="flex justify-between items-center mt-6">
                <button type="button" onclick="window.location.href='{{ route('Crear.roles') }}'"
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                    Cancelar
                </button>

                <div class="flex space-x-4">
                    <button type="reset" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                        Vaciar
                    </button>
                    <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition transform hover:scale-105 duration-300">
                        <span id="submitText">Siguiente</span>
                        <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2" style="display: none;"></i>
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
        let timeoutDuration = 2 * 60 * 1000;
        let warningDuration = 1 * 60 * 1000;
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

        function disableSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            submitBtn.classList.remove('hover:bg-blue-600', 'hover:scale-105');

            submitText.textContent = 'Procesando...';
            submitSpinner.style.display = 'inline-block';
        }

        window.addEventListener('DOMContentLoaded', () => {
            startTimers();

            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordError = document.getElementById('passwordError');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('adminForm');

            function validatePasswords() {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    passwordError.classList.remove('hidden');
                    confirmPasswordInput.classList.add('border-red-500');
                    confirmPasswordInput.classList.remove('border-blue-300');
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    return false;
                } else {
                    passwordError.classList.add('hidden');
                    confirmPasswordInput.classList.remove('border-red-500');
                    confirmPasswordInput.classList.add('border-blue-300');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    return true;
                }
            }

            confirmPasswordInput.addEventListener('input', validatePasswords);
            passwordInput.addEventListener('input', validatePasswords);

            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                if (!validatePasswords()) {
                    e.preventDefault();
                    return false;
                }

                const username = document.getElementById('name').value;
                if (username.length > 15) {
                    e.preventDefault();
                    alert('El nombre de usuario no puede exceder los 15 caracteres');
                    return false;
                }

                disableSubmitButton();
                isSubmitting = true;
                return true;
            });

            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && isSubmitting) {
                    e.preventDefault();
                }
            });

            document.getElementById('name').addEventListener('input', function(e) {
                if (e.target.value.length > 15) {
                    e.target.value = e.target.value.substring(0, 15);
                }
            });
        });

        ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
            window.addEventListener(evt, () => {
                resetTimers();
                closeModal();
            });
        });

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
