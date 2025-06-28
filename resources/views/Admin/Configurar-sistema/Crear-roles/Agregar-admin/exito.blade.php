<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Añadir estudiante nuevo</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-200">
  <div class="max-w-4xl mx-auto mt-8 bg-white shadow-md rounded-lg">
    <!-- Header -->
    <div class="bg-white text-blue-700 px-6 py-4 rounded-t-lg flex items-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-12 h-12 mr-4">
        <h1 class="text-xl font-bold text-blue-800 text-center flex-grow pr-16">Crear administrador</h1>
    </div>

    <!-- Contenido -->
    <div class="p-8 text-center">
      <p class="text-lg font-medium text-blue-900 mb-6">¡Administrador creado con éxito!</p>
      <div class="flex justify-center">
        <!-- Nuevo Check SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
    </div>
  </div>
  <script>
    setTimeout(function () {
        window.location.href = "{{ route('Admin.index') }}"; 
    }, 5000); 
</script>
</body>
</html>
