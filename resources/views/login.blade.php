<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BioSystem | Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->


</head>
<body class="bg-blue-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-md shadow-md p-10 w-full max-w-lg animate-fade-in">
        
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-4">
            <h1 class="text-3xl font-extrabold text-blue-800 tracking-wide text-center">Iniciar sesión</h1>
            <p class="text-sm text-gray-500 text-center mt-2">Accede a tu cuenta de forma segura</p>
        </div>

        @if(session('logout_reason'))
            <div class="flex items-center bg-red-100 border border-red-400 text-red-800 text-sm font-semibold px-6 py-4 rounded-lg shadow-md mb-6 animate-fade-in">
                <i class="fas fa-exclamation-triangle text-xl mr-3 text-yellow-300 animate-bounce"></i>
                <span class="text-center w-full">{{ session('logout_reason') }}</span>
            </div>
        @endif



       
        <div class="flex flex-col items-center space-y-4">
            <button onclick="window.location.href='{{ route('authenticate.fingerprint')}}'" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all duration-300">
                <i class="fas fa-fingerprint mr-2"></i> Iniciar sesión con huella
            </button>
            <button onclick="window.location.href='{{ route('credenciales')}}'" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all duration-300">
                <i class="fas fa-key mr-2"></i> Iniciar sesión con contraseña
            </button>
        </div>
    </div>
    
        


  
    </div>
</body>
</html>