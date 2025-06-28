<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportesController extends Controller
{
    public function index()
    {
        return view('Admin.Configurar-sistema.Reportes.index');
    }
    
    public function estudiantes()
    {
        try {
            $datos = DB::table('historial_accesos')
                ->select('id', 'estudiante_id', 'tipo_accion', 'descripcion', 'fecha_hora')
                ->orderBy('fecha_hora', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $datos,
                'total' => $datos->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
    
    public function usuarios()
    {
        try {
            $datos = DB::table('historial_usuarios')
                ->select('id', 'users_id', 'tipo_accion', 'descripcion', 'fecha_hora')
                ->orderBy('fecha_hora', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $datos,
                'total' => $datos->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}