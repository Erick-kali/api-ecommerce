<?php
// app/Http/Controllers/API/ProductoPromocionController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductoPromocion;
use Illuminate\Http\Request;
use Validator;

class ProductoPromocionController extends Controller
{
    // Obtener todos los productos asociados a promociones
    public function index()
    {
        $productosPromocion = ProductoPromocion::with(['producto', 'promocion'])->get();
        return response()->json($productosPromocion);
    }

    // Asociar productos a una promoción
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promocion_id' => 'required|integer|exists:promociones,id',  // Validación del ID de la promoción
            'producto_ids' => 'required|array',  // Validación para que se envíe una lista de productos
            'producto_ids.*' => 'integer|exists:productos,id',  // Validación para asegurarse de que cada producto exista
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Asignar productos a la promoción
        foreach ($request->producto_ids as $producto_id) {
            ProductoPromocion::create([
                'promocion_id' => $request->promocion_id,
                'producto_id' => $producto_id,
            ]);
        }

        return response()->json(['message' => 'Productos asociados a la promoción exitosamente'], 201);
    }

    // Eliminar una asociación entre un producto y una promoción
    public function destroy($id)
    {
        $productoPromocion = ProductoPromocion::find($id);

        if (!$productoPromocion) {
            return response()->json(['message' => 'Asociación no encontrada'], 404);
        }

        $productoPromocion->delete();

        return response()->json(['message' => 'Asociación eliminada correctamente']);
    }
}
