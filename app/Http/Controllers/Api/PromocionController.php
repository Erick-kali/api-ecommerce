<?php
// app/Http/Controllers/API/PromocionController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Validator;
use Storage;

class PromocionController extends Controller
{
    // Obtener todas las promociones con filtros opcionales
    public function index(Request $request)
    {
        $query = Promocion::with('producto');
        
        // Filtrar por estado (activo/inactivo)
        if ($request->has('estado')) {
            $estado = $request->estado;
            if ($estado === 'activo') {
                $query->where('estado', 'activo');
            } elseif ($estado === 'inactivo') {
                $query->where('estado', 'inactivo');
            }
        }
        
        // Filtrar por fechas vigentes
        if ($request->has('vigentes')) {
            $hoy = now()->format('Y-m-d');
            $query->where('fecha_inicio', '<=', $hoy)
                  ->where(function($q) use ($hoy) {
                      $q->whereNull('fecha_fin')
                        ->orWhere('fecha_fin', '>=', $hoy);
                  });
        }
        
        $promociones = $query->get();
        return response()->json($promociones);
    }

    // Obtener solo promociones activas
    public function activas()
    {
        $promociones = Promocion::with('producto')
            ->where('estado', 'activo')
            ->get();
        return response()->json($promociones);
    }

    // Obtener solo promociones inactivas
    public function inactivas()
    {
        $promociones = Promocion::with('producto')
            ->where('estado', 'inactivo')
            ->get();
        return response()->json($promociones);
    }

    // Crear una nueva promoción
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'descuento' => 'required|numeric|min:0|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'producto_id' => 'required|integer|exists:productos,id',
            'imagen_url' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048',
            'estado' => 'nullable|in:activo,inactivo',
            'stock' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Manejar la carga de la imagen (si existe)
        $imagePath = null;
        if ($request->hasFile('imagen_url')) {
            $imagePath = $request->file('imagen_url')->store('promociones', 'public');
        }

        // Obtener el stock - si no se pasa, usar 0 como default
        $stock = $request->has('stock') ? $request->stock : 0;

        // Crear la promoción
        $promocion = Promocion::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'descuento' => $request->descuento,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'producto_id' => $request->producto_id,
            'imagen_url' => $imagePath,
            'estado' => $request->estado ?? 'activo',
            'stock' => $stock,
        ]);

        return response()->json([
            'message' => 'Promoción creada exitosamente',
            'promocion' => $promocion->load('producto')
        ], 201);
    }

    // Obtener una promoción por ID
    public function show($id)
    {
        $promocion = Promocion::with('producto')->find($id);

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        return response()->json($promocion);
    }

    // Actualizar una promoción existente
    public function update(Request $request, $id)
    {
        $promocion = Promocion::find($id);

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'descuento' => 'sometimes|required|numeric|min:0|max:100',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'producto_id' => 'sometimes|required|integer|exists:productos,id',
            'imagen_url' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048',
            'estado' => 'nullable|in:activo,inactivo',
            'stock' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Manejar la actualización de la imagen
        if ($request->hasFile('imagen_url')) {
            // Eliminar la imagen anterior si existe
            if ($promocion->imagen_url && Storage::disk('public')->exists($promocion->imagen_url)) {
                Storage::disk('public')->delete($promocion->imagen_url);
            }
            
            // Guardar la nueva imagen
            $imagePath = $request->file('imagen_url')->store('promociones', 'public');
            $promocion->imagen_url = $imagePath;
        }

        // Actualizar los campos
        if ($request->has('nombre')) {
            $promocion->nombre = $request->nombre;
        }
        if ($request->has('descripcion')) {
            $promocion->descripcion = $request->descripcion;
        }
        if ($request->has('descuento')) {
            $promocion->descuento = $request->descuento;
        }
        if ($request->has('fecha_inicio')) {
            $promocion->fecha_inicio = $request->fecha_inicio;
        }
        if ($request->has('fecha_fin')) {
            $promocion->fecha_fin = $request->fecha_fin;
        }
        if ($request->has('producto_id')) {
            $promocion->producto_id = $request->producto_id;
        }
        if ($request->has('estado')) {
            $promocion->estado = $request->estado;
        }
        // Solo actualizar stock si se pasa en la request
        if ($request->has('stock')) {
            $promocion->stock = $request->stock;
        }

        $promocion->save();

        return response()->json([
            'message' => 'Promoción actualizada exitosamente',
            'promocion' => $promocion->load('producto')
        ]);
    }

    // Activar una promoción
    public function activar($id)
    {
        $promocion = Promocion::find($id);

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        $promocion->estado = 'activo';
        $promocion->save();

        return response()->json([
            'message' => 'Promoción activada exitosamente',
            'promocion' => $promocion->load('producto')
        ]);
    }

    // Desactivar una promoción
    public function desactivar($id)
    {
        $promocion = Promocion::find($id);

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        $promocion->estado = 'inactivo';
        $promocion->save();

        return response()->json([
            'message' => 'Promoción desactivada exitosamente',
            'promocion' => $promocion->load('producto')
        ]);
    }

    // Eliminar una promoción (soft delete recomendado)
    public function destroy($id)
    {
        $promocion = Promocion::find($id);

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        // Eliminar la imagen si existe
        if ($promocion->imagen_url && Storage::disk('public')->exists($promocion->imagen_url)) {
            Storage::disk('public')->delete($promocion->imagen_url);
        }

        $promocion->delete();

        return response()->json(['message' => 'Promoción eliminada exitosamente']);
    }

    // Restaurar una promoción eliminada (si usas soft deletes)
    public function restore($id)
    {
        $promocion = Promocion::withTrashed()->find($id);

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        if (!$promocion->trashed()) {
            return response()->json(['message' => 'La promoción no está eliminada'], 400);
        }

        $promocion->restore();

        return response()->json([
            'message' => 'Promoción restaurada exitosamente',
            'promocion' => $promocion->load('producto')
        ]);
    }

    // Obtener promociones eliminadas (papelera)
    public function papelera()
    {
        $promociones = Promocion::onlyTrashed()->with('producto')->get();
        return response()->json($promociones);
    }

    // Obtener estadísticas de promociones
    public function estadisticas()
    {
        $totalPromociones = Promocion::count();
        
        $promocionesActivas = Promocion::where('estado', 'activo')->count();
        $promocionesInactivas = Promocion::where('estado', 'inactivo')->count();
        
        // Promociones vigentes por fecha
        $hoy = now()->format('Y-m-d');
        $promocionesVigentes = Promocion::where('fecha_inicio', '<=', $hoy)
            ->where(function($q) use ($hoy) {
                $q->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);
            })
            ->where('estado', 'activo')
            ->count();
        
        // Promociones vencidas
        $promocionesVencidas = Promocion::where('fecha_fin', '<', $hoy)
            ->where('estado', 'activo')
            ->count();
        
        // Promociones eliminadas
        $promocionesEliminadas = Promocion::onlyTrashed()->count();

        return response()->json([
            'total_promociones' => $totalPromociones,
            'promociones_activas' => $promocionesActivas,
            'promociones_inactivas' => $promocionesInactivas,
            'promociones_vigentes' => $promocionesVigentes,
            'promociones_vencidas' => $promocionesVencidas,
            'promociones_eliminadas' => $promocionesEliminadas,
            'porcentaje_activas' => $totalPromociones > 0 ? round(($promocionesActivas / $totalPromociones) * 100, 2) : 0,
            'porcentaje_inactivas' => $totalPromociones > 0 ? round(($promocionesInactivas / $totalPromociones) * 100, 2) : 0,
        ]);
    }

    // Obtener conteo simple (solo activas vs inactivas)
    public function conteo()
    {
        $activas = Promocion::where('estado', 'activo')->count();
        $inactivas = Promocion::where('estado', 'inactivo')->count();
        
        return response()->json([
            'activas' => $activas,
            'inactivas' => $inactivas,
            'total' => $activas + $inactivas
        ]);
    }
}