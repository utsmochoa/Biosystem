<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\estudiantes;
use App\Models\User;
use App\Models\reportesUsuarios;
use App\Models\reportesEstudiantes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class AdminController extends Controller
{
    public function index(){
        return view('Admin.index');
    }



    public function añadirEstudianteExistente(Request $request){
        $query = $request->input('search');

        $idsConHuella = DB::table('huellas_digitales')->pluck('estudiante_id');

        $estudiantes = estudiantes::when($query, function ($q) use ($query) {
                $q->where('nombre', 'like', '%' . $query . '%')
                  ->orWhere('apellido', 'like', '%' . $query . '%')
                  ->orWhere('cedula', 'like', '%' . $query . '%');
            })
            ->whereNotIn('id', $idsConHuella)
            ->where('activo', true)
            ->get();

        return view('Admin.Gestion-estudiante.Agregar-estudiante.Agregar-huella-existente.index', compact('estudiantes', 'query'));
    }

    public function capturarHuellaEstudiante(Request $request, $id)
    {
        try {
            // Validar que el estudiante exista y esté activo
            $estudiante = estudiantes::where('id', $id)->where('activo', true)->first();
    
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado o inactivo'
                ], 404);
            }
    
            // Ejecutar script Python
            $output = [];
            $returnCode = null;
            $pythonScript = base_path('resources/python/agregarHuellaExistente.py');
            $command = "python \"$pythonScript\" $id 2>&1";
            exec($command, $output, $returnCode);
    
            // Buscar JSON en la salida del script
            $jsonResult = null;
            foreach (array_reverse($output) as $line) {
                $decoded = json_decode($line, true);
                if ($decoded !== null && isset($decoded['success'])) {
                    $jsonResult = $decoded;
                    break;
                }
            }
    
            if ($jsonResult) {
                // Registrar reporte
                reportesEstudiantes::create([
                    'estudiante_id' => $id,
                    'tipo_accion' => 'registro',
                    'descripcion' => $jsonResult['success']
                        ? 'Huella registrada exitosamente.'
                        : 'Fallo en el registro de huella.',
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);
    
                return response()->json([
                    'success' => $jsonResult['success'],
                    'message' => $jsonResult['message'],
                    'estudiante' => [
                        'id' => $estudiante->id,
                        'nombres' => $estudiante->nombres,
                        'apellidos' => $estudiante->apellidos,
                        'cedula' => $estudiante->cedula_identidad
                    ]
                ], $jsonResult['success'] ? 200 : 400);
            }
    
            // Si no se recibió respuesta JSON válida
            $errorOutput = implode("\n", $output);
            return response()->json([
                'success' => false,
                'message' => 'Error en el script de captura. Código: ' . $returnCode . '. Salida: ' . $errorOutput
            ], 500);
    
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function gestionEstudiante(){
        return view('Admin.Gestion-estudiante.index');
    }

    public function seleccionAñadirNuevoEstudiante(){
        return view('Admin.Gestion-estudiante.Agregar-estudiante.index');
    }



    public function pedirDatosNuevo(){
        return view('Admin.Gestion-estudiante.Agregar-estudiante.Agregar-nuevo-estudiante.index');
    }

    public function NuevoEstudianteExito(){
        return view('Admin.Gestion-estudiante.Agregar-estudiante.Agregar-nuevo-estudiante.exito');
    }

    public function store(Request $request){
        if ($request->session()->has('registro_en_proceso')) {
            return back()->with('error', 'Ya se está procesando un registro. Espera unos segundos...');
        }
    
        $request->session()->put('registro_en_proceso', true);
    
        DB::beginTransaction();
    
        try {
            if (estudiantes::where('cedula_identidad', $request->cedula_identidad)->exists()) {
                $request->session()->forget('registro_en_proceso');
                return back()->withInput()->with('error', 'Ya existe un estudiante con esta cédula.');
            }
    
            $estudiante = new estudiantes();
            $estudiante->nombres = $request->nombres;
            $estudiante->apellidos = $request->apellidos;
            $estudiante->cedula_identidad = $request->cedula_identidad;
            $estudiante->carrera = $request->carrera;
            $estudiante->semestre = $request->semestre;
            $estudiante->activo = true;
    
            if ($request->hasFile('foto')) {
                $estudiante->foto = file_get_contents($request->file('foto')->getRealPath());
            }
    
            $estudiante->save();
            $estudiante->refresh();
    
    
            DB::commit(); // Se guarda aquí para que Python lo vea
    
            // ───── EJECUTAR SCRIPT PYTHON ─────
            $pythonScript = base_path('resources/python/agregarEstudiante.py');
            $command = escapeshellcmd("python \"$pythonScript\" \"{$estudiante->id}\"");
            $output = [];
            $return_var = null;
            exec($command, $output, $return_var);
    
            $jsonResult = null;
            foreach (array_reverse($output) as $line) {
                $decoded = json_decode($line, true);
                if ($decoded !== null && isset($decoded['success'])) {
                    $jsonResult = $decoded;
                    reportesEstudiantes::create([
                        'estudiante_id' => $estudiante->id,
                        'tipo_accion' => 'registro',
                        'descripcion' => 'Registro de nuevo estudiante exitoso.',
                        'fecha_hora' => Carbon::now('America/Caracas'),
                    ]);
                    break;
                }
            }
    
            if (!$jsonResult || !$jsonResult['success']) {
                // 🔁 Si falla la huella, eliminar estudiante y log
                DB::beginTransaction();
    
                // Borrar registros relacionados
                reportesEstudiantes::where('estudiante_id', $estudiante->id)->delete();
                $estudiante->delete();
    
                DB::commit();
    
                $request->session()->forget('registro_en_proceso');
    
                return back()->withInput()->with('error', $jsonResult['message'] ?? 'Error al capturar huella. Registro revertido.');
            }
    
            $request->session()->forget('registro_en_proceso');
    
            return view('Admin.Gestion-estudiante.Agregar-estudiante.Agregar-nuevo-estudiante.exito');
    
        } catch (\Exception $e) {
            DB::rollBack();
            $request->session()->forget('registro_en_proceso');
            return back()->withInput()->with('error', 'Error al procesar el registro: ' . $e->getMessage());
        }
    }
    

    
    

    public function actualizarEstudiante(){
        $estudiantes = estudiantes::where('activo', true)->get();
        return view('Admin.Gestion-estudiante.Actualizar-estudiante.index', compact('estudiantes'));
    }

    public function actualizarDatos($id, Request $request)
    {
        $nombres = $request->query('nombres');
        $apellidos = $request->query('apellidos');
        $cedula = $request->query('cedula');
        $carrera = $request->query('carrera');
        $semestre = $request->query('semestre');

        return view('Admin.Gestion-estudiante.Actualizar-estudiante.datos', compact('id', 'nombres', 'apellidos', 'cedula', 'carrera', 'semestre'));
    }

    public function procesarActualizacion(Request $request, $id)
    {
        $request->validate([
            'nombres' => 'string|max:255',
            'apellidos' => 'string|max:255',
            'cedula_identidad' => 'string|max:20',
            'carrera' => 'string|max:255',
            'semestre' => 'string|max:20'
        ]);

        $estudiante = estudiantes::findOrFail($id);

        $updateFields = $request->input('update_fields', []);

        foreach ($updateFields as $field) {
            if ($request->has($field)) {
                $estudiante->$field = $request->input($field);
            }
        }

        $estudiante->save();

        reportesEstudiantes::create([
            'estudiante_id' => $id,
            'tipo_accion' => 'actualizacion',
            'descripcion' => 'Actualizacion de datos de estudiante exitosa.',
            'fecha_hora' => Carbon::now('America/Caracas'),
        ]);

        return redirect()->route('Gestion.actualizar')
            ->with('success', 'Datos del estudiante actualizados correctamente');
    }

    public function capturarHuella(Request $request){
        $id = $request->input('estudiante_id');
        $output = [];
        $return_var = null;

        $pythonScript = base_path('resources/python/actualizarEstudiante.py');

        $command = "python \"$pythonScript\" $id 2>&1";

        exec($command, $output, $return_var);

        $jsonResult = null;
        foreach (array_reverse($output) as $line) {
            $decoded = json_decode($line, true);
            if ($decoded !== null && isset($decoded['success'])) {
                $jsonResult = $decoded;
                break;
            }
        }

        if ($jsonResult) {
            if ($jsonResult['success']) {
                reportesEstudiantes::create([
                    'estudiante_id' => $id,
                    'tipo_accion' => 'actualizacion',
                    'descripcion' => 'Actualizacion de huella de estudiante exitosa.',
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);

                return redirect()->route('Gestion.actualizar')
                    ->with('success', $jsonResult['message']);
            } else {
                reportesEstudiantes::create([
                    'estudiante_id' => $id,
                    'tipo_accion' => 'actualizacion',
                    'descripcion' => 'Error en actualizacion de huella de estudiante.',
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);

                return redirect()->route('Gestion.actualizar')
                    ->with('error', $jsonResult['message']);
            }
        }

        $errorOutput = implode("\n", $output);
        return redirect()->route('Gestion.actualizar')
            ->with('error', 'Error en el script de captura. Código: ' . $return_var . '. Salida: ' . $errorOutput);
    }

    // Función principal para mostrar la vista
    public function eliminarEstudiante(){
        $estudiantesHabilitados = estudiantes::where('activo', true)->get();
        $estudiantesDeshabilitados = estudiantes::where('activo', false)->get();
        
        return view('Admin.Gestion-estudiante.Borrar-estudiante.index', compact('estudiantesHabilitados', 'estudiantesDeshabilitados'));
    }

    // Función para deshabilitar estudiante
    public function deshabilitarEstudiante(Request $request, $id)
    {
        $request->validate([
            'razon' => 'required|string|max:255'
        ]);

        $estudiante = estudiantes::findOrFail($id);
        $estudiante->activo = false;
        $estudiante->save();

        // Crear registro en el historial
        reportesEstudiantes::create([
            'estudiante_id' => $id,
            'tipo_accion' => 'deshabilitacion',
            'descripcion' => 'Estudiante deshabilitado. Razón: ' . $request->razon,
            'fecha_hora' => Carbon::now('America/Caracas'),
        ]);

        return redirect()->route('Gestion.eliminar')
            ->with('success', 'Estudiante deshabilitado correctamente');
    }

    // Función para habilitar estudiante
    public function habilitarEstudiante(Request $request, $id)
    {
        $request->validate([
            'razon' => 'required|string|max:255'
        ]);

        $estudiante = estudiantes::findOrFail($id);
        $estudiante->activo = true;
        $estudiante->save();

        // Crear registro en el historial
        reportesEstudiantes::create([
            'estudiante_id' => $id,
            'tipo_accion' => 'habilitacion',
            'descripcion' => 'Estudiante habilitado. Razón: ' . $request->razon,
            'fecha_hora' => Carbon::now('America/Caracas'),
        ]);

        return redirect()->route('Gestion.eliminar')
            ->with('success', 'Estudiante habilitado correctamente');
    }

    // Función destroy original (puedes mantenerla o eliminarla)
    public function destroy($id)
    {
        $estudiante = estudiantes::findOrFail($id);
        $estudiante->activo = false;
        $estudiante->save();

        return redirect()->route('Gestion.eliminar')
            ->with('success', 'Estudiante deshabilitado correctamente');
    }

    public function configurarSistema(){
        return view('Admin.Configurar-sistema.index');
    }

    public function crearRolesYPermisos(){
        return view('Admin.Configurar-sistema.Crear-roles.index');
    }

    public function agregarAdministrador(){
        return view('Admin.Configurar-sistema.Crear-roles.Agregar-admin.index');
    }


    public function storeAdmin(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();

        try {
            $admin = new User();
            $admin->name = $request->name;
            $admin->password = bcrypt($request->password);
            $admin->rol = 'admin';
            $admin->save();

            $admin->refresh();

            reportesUsuarios::create([
                'users_id' => $admin->id,
                'tipo_accion' => 'registro',
                'descripcion' => 'Administrador registrado con huella digital.',
                'fecha_hora' => Carbon::now('America/Caracas'),
            ]);

            DB::commit();

            $id = $admin->id;
            $pythonScript = base_path('resources/python/agregarAdministrador.py');
            $command = escapeshellcmd("python \"$pythonScript\" \"$id\"");
            $output = [];
            $return_var = null;

            exec($command, $output, $return_var);

            $jsonResult = null;
            foreach (array_reverse($output) as $line) {
                $decoded = json_decode($line, true);
                if ($decoded !== null && isset($decoded['success'])) {
                    $jsonResult = $decoded;
                    break;
                }
            }

            if (!$jsonResult || !$jsonResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $jsonResult['message'] ?? 'Error desconocido en captura de huella');
            }

            return redirect()->route('Agregar.admin.exito')
                ->with('success', 'Administrador agregado con huella digital.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar administrador: ' . $e->getMessage());
        }
    }

    public function exitoAdmin(){
        return view('Admin.Configurar-sistema.Crear-roles.Agregar-admin.exito');
    }

    public function agregarPersonalSeguridad(){
        return view('Admin.Configurar-sistema.Crear-roles.Agregar-personal.index');
    }
    
    public function storePersonal(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);
    
        try {
            $personal = new User();
            $personal->name = $request->name;
            $personal->password = bcrypt($request->password);
            $personal->rol = 'operador';
            $personal->save();
    
            $output = [];
            $return_var = null;
    
            $generatedId = $personal->id;
    
            $pythonScript = base_path('resources/python/agregarPersonal.py');
            $command = "python \"$pythonScript\" $generatedId 2>&1";
    
            exec($command, $output, $return_var);
    
            $jsonResult = null;
            foreach (array_reverse($output) as $line) {
                $decoded = json_decode($line, true);
                if ($decoded !== null && isset($decoded['success'])) {
                    $jsonResult = $decoded;
                    break;
                }
            }
    
            if (!$jsonResult || !$jsonResult['success']) {
                $personal->delete();
    
                $errorMessage = $jsonResult['message'] ?? 'Error desconocido en el proceso de captura de huella';
    
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }
    
            DB::beginTransaction();
    
            reportesUsuarios::create([
                'users_id' => $personal->id,
                'tipo_accion' => 'registro',
                'descripcion' => 'Registro de personal de seguridad completado exitosamente.',
                'fecha_hora' => Carbon::now('America/Caracas'),
            ]);
    
            DB::commit();
    
            return redirect()->route('Agregar.personal.exito')
                ->with('success', 'Personal de seguridad agregado correctamente con huella digital registrada.');
            
        } catch (\Exception $e) {
            if (isset($personal) && $personal->exists) {
                $personal->delete();
            }
    
            DB::rollBack();
    
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar el registro del personal: ' . $e->getMessage());
        }
    }

    public function exitoPersonal(){
        return view('Admin.Configurar-sistema.Crear-roles.Agregar-personal.exito');
    }

    public function configurarDispositivo(){
        return view('Admin.Configurar-sistema.Configurar-dispositivo.index');
    }
    /**
     * API endpoint para obtener la lista de dispositivos
     */

    public function getDevices(): JsonResponse
    {
        try {
            // Ejecutar el script de Python
            $pythonScript = base_path('resources/python/estatusDispositivo.py');
            
            // Verificar que el script existe
            if (!file_exists($pythonScript)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Script de Python no encontrado',
                    'devices' => []
                ], 404);
            }

            // Crear el proceso para ejecutar el script
            $process = new Process(['python', $pythonScript]);
            $process->setTimeout(30); // Timeout de 30 segundos
            
            // Ejecutar el proceso
            $process->run();

            // Verificar si el proceso fue exitoso
            if (!$process->isSuccessful()) {
                Log::error('Error ejecutando script Python: ' . $process->getErrorOutput());
                
                return response()->json([
                    'success' => false,
                    'error' => 'Error al ejecutar el script de verificación',
                    'devices' => []
                ], 500);
            }

            // Obtener la salida del script
            $output = $process->getOutput();
            
            // Intentar decodificar la respuesta JSON
            $data = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decodificando JSON del script Python: ' . json_last_error_msg());
                Log::error('Salida del script: ' . $output);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Error al procesar la respuesta del dispositivo',
                    'devices' => []
                ], 500);
            }

            // Verificar si el script devolvió datos válidos
            if (!isset($data['success']) || !$data['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error'] ?? 'Error desconocido del dispositivo',
                    'devices' => []
                ], 500);
            }

            // Procesar y formatear los datos de dispositivos
            $devices = [];
            if (isset($data['devices']) && is_array($data['devices'])) {
                foreach ($data['devices'] as $device) {
                    $devices[] = [
                        'id' => $device['id'] ?? 'N/A',
                        'name' => $device['name'] ?? 'Dispositivo desconocido',
                        'status' => $this->mapDeviceStatus($device['status'] ?? 'unknown')
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'devices' => $devices,
                'timestamp' => now()->toISOString()
            ]);

        } catch (ProcessFailedException $e) {
            Log::error('Error en proceso Python: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error de comunicación con el dispositivo',
                'devices' => []
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error general en getDevices: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'devices' => []
            ], 500);
        }
    }

    /**
     * API endpoint para obtener detalles específicos de un dispositivo
     */
    public function getDeviceDetails(): JsonResponse
    {
        try {
            $pythonScript = base_path('resources/python/detallesDispositivo.py');
    
            if (!file_exists($pythonScript)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Script de Python no encontrado'
                ], 404);
            }
    
            // Ejecuta el proceso con un entorno limpio
            $process = new Process(['python', $pythonScript]);
            $process->setTimeout(30);
            $process->run();
    
            // Obtiene la salida limpia
            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());
    
            // Si hay contenido en stderr, considera que es un error
            if (!empty($errorOutput)) {
                Log::error("Error en script Python: $errorOutput");
                return response()->json([
                    'success' => false,
                    'error' => 'Error en el script Python',
                    'details' => $errorOutput
                ], 500);
            }
    
            // Intenta decodificar el JSON directamente
            $data = json_decode($output, true);
    
            // Si falla, intenta limpiar posibles escapes
            if (json_last_error() !== JSON_ERROR_NONE) {
                $cleanedOutput = stripslashes($output);
                $data = json_decode($cleanedOutput, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Error decodificando JSON: ' . json_last_error_msg());
                    return response()->json([
                        'success' => false,
                        'error' => 'Respuesta inválida del dispositivo',
                        'raw_output' => $output
                    ], 500);
                }
            }
    
            // Verifica si el script Python reportó éxito
            if (isset($data['success']) && $data['success'] === true) {
                return response()->json([
                    'success' => true,
                    'status' => $data['status'] ?? 'conectado',
                    'message' => $data['message'] ?? 'Dispositivo conectado',
                    'details' => $this->formatDeviceDetailsFromPythonOutput($data),
                    'timestamp' => now()->toISOString()
                ]);
            }
    
            // Si llegamos aquí, hubo un error no capturado
            return response()->json([
                'success' => false,
                'error' => $data['error'] ?? 'Error desconocido',
                'details' => $data['details'] ?? null
            ], 500);
    
        } catch (\Exception $e) {
            Log::error('Excepción en getDeviceDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'exception' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Mapear el estado del dispositivo a valores estandarizados
     */
    private function mapDeviceStatus(string $status): string
    {
        $statusMap = [
            'connected' => 'Conectado',
            'disconnected' => 'Desconectado',
            'error' => 'Error',
            'busy' => 'Ocupado',
            'unknown' => 'Desconocido'
        ];

        return $statusMap[strtolower($status)] ?? 'Desconocido';
    }

    /**
     * Formatear los detalles del dispositivo para la vista
     */
    private function formatDeviceDetailsFromPythonOutput(array $data): array
    {
        // Accede a los detalles dentro de la estructura completa
        $details = $data['details'] ?? $data;
        
        return [
            'device' => [
                'id' => $details['device_info']['id'] ?? 'N/A',
                'name' => $details['device_info']['name'] ?? 'N/A',
                'model' => $details['device_info']['model'] ?? 'N/A',
                'serial' => $details['device_info']['serial_number'] ?? 'N/A',
                'resolution' => $details['device_info']['resolution'] ?? 'N/A'
            ],
            'versions' => [
                'api' => $details['version_info']['FTRAPI_version'] ?? 'N/A',
                'driver' => $details['version_info']['ftrScanAPI_version'] ?? 'N/A'
            ],
            'status' => [
                'sensor' => $details['sensor_info']['sensor_active'] ?? false,
                'finger' => $details['sensor_info']['finger_present'] ?? false,
                'connection' => $details['connection_info']['driver_status'] ?? 'N/A'
            ],
            'system' => [
                'os' => $details['connection_info']['system_info']['os'] ?? 'N/A',
                'arch' => $details['connection_info']['system_info']['architecture'] ?? 'N/A',
                'port' => $details['connection_info']['port'] ?? 'N/A'
            ]
        ];
    }


    public function probarDispositivo(){
        // Ruta al script de Python
        $pythonScript = base_path('resources/python/probarDispositivo.py');
        $pythonExecutable = 'python'; // o 'python3' dependiendo de tu configuración
        
        // Ejecutar el script
        $output = [];
        $returnCode = 0;
        
        exec("$pythonExecutable $pythonScript 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            return redirect()->route('Configurar.dispositivo')
                ->with('success', 'Prueba ejecutada con éxito. El dispositivo está conectado y funcionando correctamente.')
                ->with('output', implode("\n", $output));

        } else {
            if ($returnCode === 1) {
                $mensaje = 'El dispositivo no está conectado o no se pudo encontrar.';
            } else{
                $mensaje = 'Error desconocido al ejecutar el script. Código de retorno: ' . $returnCode;
            }
            return redirect()->route('Configurar.dispositivo')
                ->with('error', 'Error al ejecutar el script. ' . $mensaje)
                ->with('output', implode("\n", $output));
        }
    }


    public function verDispositivoConectado(){
        return view('Admin.Configurar-sistema.Configurar-dispositivo.Ver-dispositivos.index');
    }



    public function gestionarDeudas()
    {
        $estudiantes = estudiantes::select('id', 'nombres', 'apellidos', 'cedula_identidad', 'carrera', 'deuda')
                                 ->where('activo', true)
                                 ->orderBy('apellidos')
                                 ->get();
        
        return view('Admin.Gestion-estudiante.Gestionar-deudas.index', compact('estudiantes'));
    }

    public function actualizarDeuda(Request $request, $id)
    {
        $request->validate([
            'deuda' => 'required|numeric|min:0|max:99999.99'
        ]);

        $estudiante = estudiantes::findOrFail($id);
        $estudiante->deuda = $request->deuda;
        $estudiante->save();

        return redirect()->route('gestionar.deudas')
                       ->with('success', 'Deuda actualizada correctamente para ' . $estudiante->nombres . ' ' . $estudiante->apellidos);
    }
}
