<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Estudiante</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body >
<!-- resources/views/components/error-screen.blade.php -->
<div class="min-h-screen bg-white flex flex-col items-center justify-center p-4">
    <!-- Header with logo and text -->
    <div class="w-full max-w-4xl mb-16">
        <div class="flex items-center justify-center gap-4">
            <!-- Logo institucional -->
            <div class="w-16 flex-shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa">
            </div>
            <!-- Título del sistema -->
            <div class="flex-1 text-center">
                <h1 class="text-[#003366] text-xl font-semibold">Sistema de Ingreso por Tecnología Dactilar IUTAJS</h1>
            </div>
        </div>
    </div>

    <!-- Error message -->
    <div class="flex flex-col items-center space-y-4">
        <p class="text-gray-700 text-lg">Por favor</p>
        <p class="text-gray-700 text-lg">Intentelo de nuevo</p>
        
        <!-- Error icon -->
        <div class="w-24 h-24 bg-[#4A90E2] rounded-lg flex items-center justify-center p-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-[#FF4B4B]" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </div>
    </div>
</div>
</body>
</html>


