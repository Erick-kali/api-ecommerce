<?php
// app/Http/Controllers/API/CarritoController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use Illuminate\Http\Request;
use Validator;

class CarritoController extends Controller
{
    // Obtener los productos en el carrito de un usuario
    public function index($usuario_id)
    {
        $carrito = Carrito::with('producto')->where('usuario_id', $usuario_id)->get();
        
        if ($carrito->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 404);
        }

        return response()->json($carrito);
    }

    // Añadir un producto al carrito
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|integer|exists:usuarios,id',  // Validación del ID del usuario
            'producto_id' => 'required|integer|exists:productos,id',  // Validación del ID del producto
            'cantidad' => 'required|integer|min:1',  // Validación de la cantidad (debe ser mayor o igual a 1)
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Verificar si el producto ya está en el carrito del usuario
        $carrito = Carrito::where('usuario_id', $request->usuario_id)
                          ->where('producto_id', $request->producto_id)
                          ->first();

        if ($carrito) {
            // Si el producto ya está en el carrito, actualizar la cantidad
            $carrito->cantidad += $request->cantidad;
            $carrito->save();
            return response()->json($carrito);
        }

        // Si el producto no está en el carrito, agregarlo
        $carrito = Carrito::create([
            'usuario_id' => $request->usuario_id,
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad,
        ]);

        return response()->json($carrito, 201);
    }

    // Actualizar la cantidad de un producto en el carrito
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',  // Validación de la cantidad
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Buscar el producto en el carrito
        $carrito = Carrito::find($id);

        if (!$carrito) {
            return response()->json(['message' => 'Producto no encontrado en el carrito'], 404);
        }

        // Actualizar la cantidad
        $carrito->cantidad = $request->cantidad;
        $carrito->save();

        return response()->json($carrito);
    }

    // Eliminar un producto del carrito
    public function destroy($id)
    {
        $carrito = Carrito::find($id);

        if (!$carrito) {
            return response()->json(['message' => 'Producto no encontrado en el carrito'], 404);
        }

        $carrito->delete();

        return response()->json(['message' => 'Producto eliminado del carrito']);
    }
}
