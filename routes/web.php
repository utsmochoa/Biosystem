<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HuellaLoginController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\PersonalMiddleware;
use App\Http\Controllers\ReportesController;

//execute php artisan php artisan migrate --seed

Route::aliasMiddleware('admin', AdminMiddleware::class);
Route::aliasMiddleware('operador', PersonalMiddleware::class);


Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/credenciales', [LoginController::class, 'credenciales'])->name('credenciales');
    Route::post('/login', [LoginController::class, 'login']);
});

// Rutas para autenticación biométrica
Route::middleware('guest')->group(function () {
    Route::get('/authenticate-fingerprint', [HuellaLoginController::class, 'startBiometricVerification'])->name('authenticate.fingerprint');
});


// Ruta de logout común
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Rutas para usuarios con rol 'operador'
    Route::middleware('auth', 'operador')->group(function () {
        //pantalla principal de ingreso de estudiantes (despues de iniciar sesion el personal de seguridad)
        
        // RUTAS ORIGINALES PARA VERIFICACIÓN POR HUELLA
        Route::get('/ingreso', [IngresoController::class, 'index'])->name('Ingreso.index');
        Route::get('/ingreso/verificar-huella', [IngresoController::class, 'verificarHuella'])->name('Ingreso.verificar');
        Route::post('/ingreso', [IngresoController::class, 'obtenerEstudianteId']);
        Route::get('/ingreso/error', [IngresoController::class, 'error'])->name('Ingreso.error');
        Route::get('/ingreso/informacion', [IngresoController::class, 'informacion']);
        Route::get('/ingreso/informacion/{estudiante_id}', [IngresoController::class, 'mostrarInformacion'])->name('Ingreso.Info');
        
        // RUTA ORIGINAL PARA BÚSQUEDA DESDE info.blade.php (modal)
        Route::post('/buscar-por-cedula', [IngresoController::class, 'buscarPorCedula'])->name('buscar.cedula');
        
        // NUEVAS RUTAS PARA VERIFICACIÓN DEDICADA POR CÉDULA
        Route::get('/ingreso/verificacion-cedula', [IngresoController::class, 'verificacionCedula'])->name('Ingreso.verificacion.cedula');
Route::post('/ingreso/verificacion-cedula', [IngresoController::class, 'buscarPorCedulaVerificacion'])->name('buscar.cedula.verificacion');
        
        // RUTA PARA LA VISTA DE INFORMACIÓN DESDE CÉDULA (si aún la necesitas)
        Route::get('/ingreso/informacion-cedula/{estudiante_id}', [IngresoController::class, 'mostrarInformacionCedula'])->name('Ingreso.Info.cedula');
    });
    
    // Rutas para usuarios con rol 'admin'
    Route::middleware('auth', 'admin')->group(function () {
        //pantalla de administradores (despues de iniciar sesion el administrador registrado)

        Route::get('/admin', [AdminController::class, 'index'])->name('Admin.index');
        Route::get('/admin/gestion-estudiante', [AdminController::class, 'gestionEstudiante'])->name('Gestion.index');

        /* añadir estudiante*/
        Route::get('/admin/gestion-estudiante/seleccion-añadir-estudiante', [AdminController::class, 'seleccionAñadirNuevoEstudiante'])->name('Gestion.seleccion');
        Route::get('/admin/gestion-estudiante/añadir-nuevo-estudiante/añadir-estudiante-existente', [AdminController::class, 'añadirEstudianteExistente'])->name('Gestion.añadirExistente');
        Route::post('/gestion/agregar-huella/capturar/{id}', [AdminController::class, 'capturarHuellaEstudiante'])->name('gestion.agregar-huella.capturar');        
        Route::get('/admin/gestion-estudiante/añadir-nuevo-estudiante/añadir-estudiante-existente/exito', [AdminController::class, 'exito']);
        Route::get('/admin/gestion-estudiante/añadir-nuevo-estudiante/añadir-estudiante-existente/error', [AdminController::class, 'error']);
        Route::get('/admin/gestion-estudiante/añadir-nuevo-estudiante/pedir-datos', [AdminController::class, 'pedirDatosNuevo'])->name('Gestion.añadirNuevoEstudiante');
        Route::post('/admin/gestion-estudiante/añadir-nuevo-estudiante/pedir-huella', [AdminController::class, 'store']);
        Route::get('/admin/gestion-estudiante/añadir-nuevo-estudiante/error', [AdminController::class, 'error']);
        Route::get('/admin/gestion-estudiante/añadir-nuevo-estudiante/exito', [AdminController::class, 'NuevoEstudianteExito'])->name('Gestion.exito');

        /* actualizar estudiante*/
        Route::get('/admin/gestion-estudiante/actualizar-estudiante', [AdminController::class, 'actualizarEstudiante'])->name('Gestion.actualizar');
        Route::get('/admin/gestion-estudiante/actualizar-estudiante/{id}/actualizar-huella', [AdminController::class, 'actualizarHuella']);
        Route::post('/capturar-huella', [AdminController::class, 'capturarHuella'])->name('capturar.huella');
        Route::get('/admin/gestion-estudiante/actualizar-estudiante/exito', [AdminController::class, 'exito']);
        Route::get('/admin/gestion-estudiante/actualizar-estudiante/error', [AdminController::class, 'error']);
        Route::get('/admin/gestion-estudiante/actualizar-estudiante/{id}/actualizar-datos', [AdminController::class, 'actualizarDatos'])->name('admin.estudiantes.editar');
        Route::put('/admin/gestion-estudiante/actualizar-estudiante/{id}', [AdminController::class, 'procesarActualizacion'])->name('admin.estudiantes.update');

        /* eliminar estudiante*/
        Route::get('/admin/gestion-estudiante/eliminar-estudiante', [AdminController::class, 'eliminarEstudiante'])->name('Gestion.eliminar');
        Route::delete('/admin/gestion-estudiante/eliminar-estudiante/{id}', [AdminController::class, 'destroy'])->name('admin.estudiantes.destroy');
        // Nueva ruta para deshabilitar estudiante
        Route::put('/admin/gestion-estudiante/deshabilitar-estudiante/{id}', [AdminController::class, 'deshabilitarEstudiante'])->name('Gestion.deshabilitar');
        Route::put('/admin/gestion-estudiante/habilitar-estudiante/{id}', [AdminController::class, 'habilitarEstudiante'])->name('Gestion.habilitar');




        Route::get('/admin/configurar-sistema/', [AdminController::class, 'configurarSistema'])->name('Configurar.index');
        Route::get('/admin/configurar-sistema/crear-roles', [AdminController::class, 'crearRolesYPermisos'])->name('Crear.roles');

        //agregar administrador
        Route::get('/admin/configurar-sistema/crear-roles/agregar-administrador', [AdminController::class, 'agregarAdministrador'])->name('Agregar.admin');
        Route::post('/admin/configurar-sistema/crear-roles/agregar-administrador/pedir-huella', [AdminController::class, 'storeAdmin'])->name('Agregar.admin.pedir-huella');
        Route::get('/admin/configurar-sistema/crear-roles/agregar-administrador/exito', [AdminController::class, 'exitoAdmin'])->name('Agregar.admin.exito');

        //agregar personal de seguridad
        Route::get('/admin/configurar-sistema/crear-roles/agregar-personal-seguridad', [AdminController::class, 'agregarPersonalSeguridad'])->name('Agregar.personal');
        Route::post('/admin/configurar-sistema/crear-roles/agregar-personal-seguridad/pedir-huella', [AdminController::class, 'storePersonal'])->name('Agregar.personal.pedir-huella');
        Route::get('/admin/configurar-sistema/crear-roles/agregar-personal-seguridad/exito', [AdminController::class, 'exitoPersonal'])->name('Agregar.personal.exito');

        //Configurar dispositivo
        Route::get('/admin/configurar-sistema/configurar-dispositivo', [AdminController::class, 'configurarDispositivo'])->name('Configurar.dispositivo');
        Route::get('/admin/configurar-sistema/configurar-dispositivo/probar-dispositivo', [AdminController::class, 'probarDispositivo'])->name('Probar.dispositivo');
        Route::get('/admin/configurar-sistema/configurar-dispositivo/ver-dispositivo-conectado', [AdminController::class, 'verDispositivoConectado'])->name('Ver.dispositivos');
        Route::get('/admin/configurar-sistema/configurar-dispositivo/ver-dispositivo-conectado/dispositivo', [AdminController::class, 'getDevices']);
        Route::get('/admin/configurar-sistema/configurar-dispositivo/ver-dispositivo-conectado/dispositivo/detalles', [AdminController::class, 'getDeviceDetails']);
        Route::get('/admin/configurar-sistema/configurar-dispositivo/ver-dispositivo-conectado/lista', [AdminController::class, 'listaDispositivoConectado']);
    
        //Generacion de reportes
        Route::get('admin/configurar-sistema/reportes', [ReportesController::class, 'index'])->name('reportes.index');
        Route::get('admin/configurar-sistema/reportes/estudiantes', [ReportesController::class, 'estudiantes'])->name('reportes.estudiantes');
        Route::get('admin/configurar-sistema/reportes/usuarios', [ReportesController::class, 'usuarios'])->name('reportes.usuarios');


        // Rutas para el "backdoor" de gestión de deudas
        Route::get('/admin/deudas', [AdminController::class, 'gestionarDeudas'])->name('gestionar.deudas');
        Route::put('/admin/estudiantes/{id}/deuda', [AdminController::class, 'actualizarDeuda'])->name('actualizar.deuda');
    });
});