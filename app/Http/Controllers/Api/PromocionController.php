<?php
// app/Http/Controllers/API/PromocionController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Validator;

class PromocionController extends Controller
{
    // Obtener todas las promociones
    public function index()
    {
        $promociones = Promocion::with('producto')->get();  // Incluye el producto relacionado
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
            'fecha_fin' => 'nullable|date',
            'producto_id' => 'required|integer|exists:productos,id',  // Validación del producto
            'imagen_url' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // Validación para aceptar imágenes
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Manejar la carga de la imagen (si existe)
        if ($request->hasFile('imagen_url')) {
            $imagePath = $request->file('imagen_url')->store('promociones', 'public');  // Guardar la imagen en la carpeta 'promociones'
        } else {
            $imagePath = null;
        }

        // Crear la promoción
        $promocion = Promocion::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'descuento' => $request->descuento,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'producto_id' => $request->producto_id,
            'imagen_url' => $imagePath,  // Guardar el path de la imagen
        ]);

        return response()->json($promocion, 201);
    }

    // Obtener una promoción por ID
    public function show($id)
    {
        $promocion = Promocion::with('producto')->find($id);  // Incluye el producto relacionado

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        return response()->json($promocion);
    }

    // Eliminar una promoción
    public function destroy($id)
    {
        $promocion = Promocion::find($id);

        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        $promocion->delete();

        return response()->json(['message' => 'Promoción eliminada']);
    }
}
