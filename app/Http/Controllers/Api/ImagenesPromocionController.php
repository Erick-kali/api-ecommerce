<?php

namespace App\Http\Controllers\API;

use App\Models\ImagenesPromocion;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
class ImagenesPromocionController extends Controller
{
    // Método para obtener todas las imágenes de promociones
    public function index()
    {
        $imagenesPromociones = ImagenesPromocion::with('promocion')->get();

        if ($imagenesPromociones->isEmpty()) {
            return response()->json(['message' => 'No se encontraron imágenes de promociones.'], 404);
        }

        return response()->json($imagenesPromociones);
    }

    // Método para obtener las imágenes de una promoción específica
    public function show($promocion_id)
    {
        $imagenesPromociones = ImagenesPromocion::where('promocion_id', $promocion_id)->get();

        if ($imagenesPromociones->isEmpty()) {
            return response()->json(['message' => 'No se encontraron imágenes para esta promoción.'], 404);
        }

        return response()->json($imagenesPromociones);
    }

    // Método para almacenar una nueva imagen de promoción
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promocion_id' => 'required|integer|exists:promociones,id',  // Validación de ID de promoción
            'imagen_url' => 'required|string|max:255',  // Validación de la URL de la imagen
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Crear nueva imagen de promoción
        $imagenPromocion = ImagenesPromocion::create([
            'promocion_id' => $request->promocion_id,
            'imagen_url' => $request->imagen_url,
        ]);

        return response()->json($imagenPromocion, 201);
    }

    // Método para eliminar una imagen de promoción
    public function destroy($id)
    {
        $imagenPromocion = ImagenesPromocion::find($id);

        if (!$imagenPromocion) {
            return response()->json(['message' => 'Imagen de promoción no encontrada'], 404);
        }

        $imagenPromocion->delete();

        return response()->json(['message' => 'Imagen de promoción eliminada']);
    }
}
