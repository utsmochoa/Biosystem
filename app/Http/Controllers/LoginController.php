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

        return back()->withErrors([
            'name' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('name');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            Log::info('Logout de usuario', ['user_id' => $user->id]);

            // Verificar si el usuario tiene un rol definido
            if (isset($user->rol) && $user->rol === 'admin') {
                reportesUsuarios::create([
                    'users_id' => $user->id,
                    'tipo_accion' => 'cierre_sesion',
                    'descripcion' => "Cierre de sesión de administrador {$user->name}.",
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);
            } elseif (isset($user->rol)) {
                reportesUsuarios::create([
                    'users_id' => $user->id,
                    'tipo_accion' => 'cierre_sesion',
                    'descripcion' => "Cierre de sesion de personal con huella de seguridad {$user->name}.",
                    'fecha_hora' => Carbon::now('America/Caracas'),
                ]);
            } else {
                Log::warning('Usuario autenticado sin rol definido.', ['user_id' => $user->id]);
            }
        } else {
            Log::warning('Intento de logout sin usuario autenticado.');
        }

        // Cerrar sesión y regenerar la sesión
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Has cerrado sesión correctamente.');

    }
}