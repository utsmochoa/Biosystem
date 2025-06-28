<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeout
{
    protected $timeout = 600; // en segundos (10 min)

    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = Session::get('lastActivityTime');
            if ($lastActivity && (time() - $lastActivity > $this->timeout)) {
                Auth::logout();
                Session::flush();
                return redirect()->route('login')->with('message', 'Sesión cerrada por inactividad.');
            }
            Session::put('lastActivityTime', time());
        }

        return $next($request);
    }
}

