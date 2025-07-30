<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\reportesUsuarios;
use Carbon\Carbon;

class HuellaLoginController extends Controller
{
    /**
     * Mostrar vista de login biométrico
     */
    public function showBiometricLogin()
    {
        return view('auth.biometric-login');
    }

    /**
     * Inicia la verificación de huella digital
     */
    public function startBiometricVerification(Request $request)
    {
        try {
            $pythonScript = base_path('resources/python/login.py');

            if (!file_exists($pythonScript)) {
                Log::error('Script no encontrado: ' . $pythonScript);
                return response()->json(['success' => false, 'error' => 'Script de verificación no encontrado'], 500);
            }

            $process = new Process(['python', $pythonScript]);
            $process->setTimeout(30);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('Error al ejecutar script: ' . $process->getErrorOutput());
                return response()->json(['success' => false, 'error' => 'Error al ejecutar script'], 500);
            }

            $output = trim($process->getOutput());
            $lines = explode("\n", $output);
            $jsonData = null;

            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if (substr($line, 0, 1) === '{' && substr($line, -1) === '}') {
                    $decoded = json_decode($line, true);
                    if ($decoded !== null) {
                        $jsonData = $decoded;
                        break;
                    }
                }
            }

            if (!$jsonData) {
                Log::error('No se pudo extraer JSON del script: ' . $output);
                return response()->json(['success' => false, 'error' => 'Respuesta no válida del verificador'], 500);
            }

            if (!empty($jsonData['success']) && !empty($jsonData['users_id'])) {
                return $this->processBiometricLogin($jsonData['users_id']);
            }

            
            return redirect()->route('login')
                ->with('logout_reason', 'Verificación fallida: ' . ($jsonData['error'] ?? 'Error desconocido'));

            return response()->json([
                'success' => false,
                'error' => $jsonData['error'] ?? 'Verificación fallida'
            ], 401);

        } catch (ProcessFailedException $e) {
            Log::error('Fallo al ejecutar proceso: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Fallo en el proceso'], 500);
        } catch (\Exception $e) {
            Log::error('Excepción general: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('logout_reason', 'Error: Se cerró automaticamente la verificacion por inactividad');
        }
    }

    /**
     * Procesa el login luego de la verificación
     */
    private function processBiometricLogin($userId)
    {
        $user = User::find($userId);
        $userName = $user->name;

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
        }



        if (!in_array($user->rol, ['admin', 'operador'])) {
            return response()->json(['success' => false, 'error' => 'Sin permisos'], 403);
        }

        Auth::login($user);
        request()->session()->regenerate();

        

        if (Auth::user()->rol === 'admin') {
            reportesUsuarios::create([
                'users_id' => $userId,
                'tipo_accion' => 'inicio_sesion',
                'descripcion' => "Inicio de sesion con huella de administrador {$userName}.",
                'fecha_hora' => Carbon::now('America/Caracas'),
            ]);
            return redirect()->route('Admin.index');
        } else {
            reportesUsuarios::create([
                'users_id' => $userId,
                'tipo_accion' => 'inicio_sesion',
                'descripcion' => "Inicio de sesion de personal con huella de seguridad {$userName}.",
                'fecha_hora' => Carbon::now('America/Caracas'),
            ]);
            return redirect()->route('Ingreso.index');
        }
    }



}
