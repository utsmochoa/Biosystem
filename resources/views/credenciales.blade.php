<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion con credenciales</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="mr-4 h-12">
            <h1 class="ml-4 text-xl font-bold text-blue-700">Iniciar sesion</h1>
            <h2> </h2>
            <h2> </h2>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        
        <form action="{{route('login')}}" method="post">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-semibold text-gray-700">Nombre de usuario:</label>
                <input type="text" id="name" name="name" class="mt-1 block w-full px-3 py-2 rounded-lg border-2 border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-200 hover:border-gray-300" required>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-semibold text-gray-700">Contraseña:</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 rounded-lg border-2 border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-200 hover:border-gray-300" required>
            </div> 

            <div class="flex justify-between space-x-4">
                <a href="{{route('login')}}" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition-colors duration-200">Cancelar</a>
                <div class="flex space-x-4">
                    <button type="reset" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">Vaciar</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Siguiente</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>