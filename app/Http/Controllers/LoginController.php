<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\reportesUsuarios;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }
    public function credenciales(){
        return view('credenciales');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // sacar id de usuario que inicio sesion
            $user = User::where('name', $credentials['name'])->first();
            $userId = $user ? $user->id : null;
            // Redireccionar según el rol
            if (Auth::user()->rol === 'admin') {

                reportesUsuarios::create([
                    'users_id' => $userId,
                    'tipo_accion' => 'inicio_sesion',
                    'descripcion' => "Inicio de sesion de administrador {$user->name}.",
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);

                return redirect()->route('Admin.index');
            } else {
                reportesUsuarios::create([
                    'users_id' => $userId,
                    'tipo_accion' => 'inicio_sesion',
                    'descripcion' => "Inicio de sesion de personal de seguridad {$user->name}.",
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);
                return redirect()->route('Ingreso.index');
            }
        }

        return redirect()->route('credenciales')->with('error', 'Credenciales incorrectas. Por favor, inténtelo de nuevo.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $inactive = $request->input('inactive', false); // Valor por defecto false

        if ($user) {
            Log::info('Logout de usuario', ['user_id' => $user->id]);

            $descripcion = '';
            if (isset($user->rol) && $user->rol === 'admin') {
                $descripcion = $inactive
                    ? "Cierre automático de sesión por inactividad de administrador {$user->name}."
                    : "Cierre de sesión de administrador {$user->name}.";
            } elseif (isset($user->rol) && $user->rol === 'operador') {
                $descripcion = $inactive
                    ? "Cierre automático de sesión por inactividad de personal de seguridad {$user->name}."
                    : "Cierre de sesion de personal de seguridad {$user->name}.";
            } else {
                Log::warning('Usuario autenticado sin rol definido.', ['user_id' => $user->id]);
                $descripcion = $inactive
                    ? "Cierre automático de sesión por inactividad de usuario {$user->name}."
                    : "Cierre de sesión de usuario {$user->name}.";
            }

            reportesUsuarios::create([
                'users_id' => $user->id,
                'tipo_accion' => 'cierre_sesion',
                'descripcion' => $descripcion,
                'fecha_hora' => Carbon::now('America/Caracas'),
            ]);
        } else {
            Log::warning('Intento de logout sin usuario autenticado.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $mensaje = $inactive
            ? 'Tu sesión ha sido cerrada automáticamente por inactividad.'
            : 'Has cerrado sesión correctamente.';

        return redirect()->route('login')->with('logout_reason', $mensaje);
    }
}
