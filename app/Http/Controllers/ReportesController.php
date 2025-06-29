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
            $datos = DB::table('historial_accesos as ha')
                ->leftJoin('estudiantes as e', 'ha.estudiante_id', '=', 'e.id')
                ->select(
                    'ha.id',
                    'ha.estudiante_id',
                    'e.nombres',
                    'e.apellidos',
                    'e.cedula_identidad as cedula',
                    'e.carrera',
                    'e.semestre',
                    'ha.tipo_accion',
                    'ha.descripcion',
                    'ha.fecha_hora',
                    DB::raw("CONCAT(COALESCE(e.nombres, ''), ' ', COALESCE(e.apellidos, '')) as nombre_completo")
                )
                ->orderBy('ha.fecha_hora', 'desc')
                ->get()
                ->map(function ($item) {
                    // Limpiar nombre completo si está vacío
                    if (trim($item->nombre_completo) === '') {
                        $item->nombre_completo = 'No disponible';
                    }
                    
                    // Asegurar que los campos no sean null
                    $item->nombres = $item->nombres ?? 'N/A';
                    $item->apellidos = $item->apellidos ?? 'N/A';
                    $item->cedula = $item->cedula ?? 'N/A';
                    $item->carrera = $item->carrera ?? 'N/A';
                    $item->semestre = $item->semestre ?? 'N/A';
                    
                    return $item;
                });
            
            return response()->json([
                'success' => true,
                'data' => $datos,
                'total' => $datos->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos de estudiantes: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
    
    public function usuarios()
    {
        try {
            $datos = DB::table('historial_usuarios as hu')
                ->leftJoin('users as u', 'hu.users_id', '=', 'u.id')
                ->select(
                    'hu.id',
                    'hu.users_id',
                    'u.name as nombre_usuario',
                    'u.rol',
                    'hu.tipo_accion',
                    'hu.descripcion',
                    'hu.fecha_hora'
                )
                ->orderBy('hu.fecha_hora', 'desc')
                ->get()
                ->map(function ($item) {
                    // Asegurar que los campos no sean null
                    $item->nombre_usuario = $item->nombre_usuario ?? 'Usuario no encontrado';
                    $item->rol = $item->rol ?? 'N/A';
                    
                    return $item;
                });
            
            return response()->json([
                'success' => true,
                'data' => $datos,
                'total' => $datos->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos de usuarios: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}