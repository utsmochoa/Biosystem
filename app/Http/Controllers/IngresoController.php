<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\estudiantes;
use Symfony\Component\Process\Process;
use App\Models\reportesEstudiantes;
use Carbon\Carbon;

class IngresoController extends Controller
{
    public function index()
    {
        return view('Ingreso.index');
    }

    public function error()
    {
        return view('Ingreso.error');
    }

    public function mostrarInformacion($id)
    {
        $estudiante = estudiantes::where('id', $id)
                                 ->where('activo', true)
                                 ->firstOrFail();
        return view('Ingreso.info', compact('estudiante'));
    }

    public function verificarHuella()
    {
        $pythonScript = base_path('resources/python/buscarEstudiante.py');

        $process = new Process(['python', $pythonScript]);
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json([
                'success' => false,
                'error' => 'No se pudo ejecutar el script de verificación'
            ]);
        }

        $output = $process->getOutput();
        $lines = explode("\n", $output);
        $jsonData = null;

        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            if (substr($line, 0, 1) === '{' && substr($line, -1) === '}') {
                $decoded = json_decode($line, true);
                if ($decoded !== null) {
                    $jsonData = $decoded;
                    break;
                }
            }
        }

        if ($jsonData === null) {
            return back()->with('error', 'La respuesta del script no es válida');
        }

        if (isset($jsonData['success']) && $jsonData['success']) {
            if (isset($jsonData['estudiante_id']) && !is_null($jsonData['estudiante_id'])) {
                $estudianteId = $jsonData['estudiante_id'];
                $estudiante = estudiantes::where('id', $estudianteId)
                                         ->where('activo', true)
                                         ->first();

                if (!$estudiante) {
                    return back()->with('error', 'Estudiante no encontrado o inactivo.');
                }

                reportesEstudiantes::create([
                    'estudiante_id' => $estudianteId,
                    'tipo_accion' => 'verificacion',
                    'descripcion' => "Verificación mediante huella de estudiante exitosa para {$estudiante->nombres}.",
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);

                return redirect("/ingreso/informacion/{$estudianteId}");
            } else {
                return back()->with('error', 'ID de estudiante no proporcionado');
            }
        } else {
            $mensaje = isset($jsonData['error']) ? $jsonData['error'] : 'Verificación fallida';
            return back()->with('error', $mensaje);
        }
    }

    public function obtenerEstudianteId(Request $request)
    {
        $estudiante_id = $request->query('estudiante_id');

        $estudiante = estudiantes::where('id', $estudiante_id)
                                 ->where('activo', true)
                                 ->first();

        if ($estudiante) {
            return response()->json($estudiante);
        } else {
            return response()->json(['message' => 'Estudiante no encontrado o inactivo'], 404);
        }
    }

    // FUNCIÓN ORIGINAL PARA BÚSQUEDA DESDE info.blade.php (mantiene funcionalidad original)
    public function buscarPorCedula(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string|max:20'
        ]);

        $cedulaIngresada = $request->input('cedula');
        $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedulaIngresada);

        $estudiante = estudiantes::where(function ($query) use ($cedulaLimpia) {
                $query->whereRaw("REPLACE(REPLACE(cedula_identidad, '.', ''), ' ', '') = ?", [$cedulaLimpia])
                      ->orWhere('cedula_identidad', $cedulaLimpia);
            })
            ->where('activo', true)
            ->first();

        if (!$estudiante) {
            $cedulaFormateada = $this->formatearCedula($cedulaLimpia);
            return back()->withInput()->with('error', 'No se encontró ningún estudiante activo con la cédula: ' . $cedulaFormateada);
        }

        $estudianteName = $estudiante->nombres . ' ' . $estudiante->apellidos;

        reportesEstudiantes::create([
            'estudiante_id' => $estudiante->id,
            'tipo_accion' => 'verificacion',
            'descripcion' => 'Verificación de estudiante por cédula de identidad para: ' . $estudianteName,
            'fecha_hora' => Carbon::now('America/Caracas'),
        ]);

        return view('Ingreso.info', compact('estudiante'))->with('success', 'Estudiante encontrado exitosamente');
    }

    // NUEVA FUNCIÓN PARA LA PANTALLA DE VERIFICACIÓN POR CÉDULA
    public function verificacionCedula()
    {
        return view('Ingreso.verificacion_cedula');
    }

    // NUEVA FUNCIÓN PARA BÚSQUEDA DESDE LA PANTALLA DE VERIFICACIÓN
    public function buscarPorCedulaVerificacion(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string|max:20'
        ]);

        $cedulaIngresada = $request->input('cedula');
        $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedulaIngresada);

        $estudiante = estudiantes::where(function ($query) use ($cedulaLimpia) {
                $query->whereRaw("REPLACE(REPLACE(cedula_identidad, '.', ''), ' ', '') = ?", [$cedulaLimpia])
                      ->orWhere('cedula_identidad', $cedulaLimpia);
            })
            ->where('activo', true)
            ->first();

        if (!$estudiante) {
            $cedulaFormateada = $this->formatearCedula($cedulaLimpia);
            // Retorna a la misma vista pero sin datos del estudiante y con error
            return view('Ingreso.verificacion_cedula')
                ->with('error', 'No se encontró ningún estudiante activo con la cédula: ' . $cedulaFormateada);
                
        }

        $estudianteName = $estudiante->nombres . ' ' . $estudiante->apellidos;

        // Crear reporte de verificación
        reportesEstudiantes::create([
            'estudiante_id' => $estudiante->id,
            'tipo_accion' => 'verificacion',
            'descripcion' => 'Verificación de estudiante por cédula desde pantalla dedicada para: ' . $estudianteName,
            'fecha_hora' => Carbon::now('America/Caracas'),
        ]);

        // Retorna a la misma vista pero con los datos del estudiante
        return view('Ingreso.verificacion_cedula', compact('estudiante'))
            ->with('success', 'Estudiante encontrado exitosamente');
    }

    private function formatearCedula($cedula)
    {
        if (strlen($cedula) == 8) {
            return number_format($cedula, 0, '', '.');
        } elseif (strlen($cedula) == 7) {
            return number_format($cedula, 0, '', '.');
        } else {
            return $cedula;
        }
    }
    
    public function mostrarInformacionCedula($id)
    {
        $estudiante = estudiantes::where('id', $id)
                                 ->where('activo', true)
                                 ->firstOrFail();
        return view('Ingreso.info_cedula', compact('estudiante'));
    }
}