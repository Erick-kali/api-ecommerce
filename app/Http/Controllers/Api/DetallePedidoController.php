<?php
// app/Http/Controllers/API/DetallePedidoController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Validator;

class DetallePedidoController extends Controller
{
    // Obtener los detalles de un pedido
    public function index($pedido_id)
    {
        $detallePedido = DetallePedido::with('producto')->where('pedido_id', $pedido_id)->get();

        if ($detallePedido->isEmpty()) {
            return response()->json(['message' => 'No se encontraron productos para este pedido.'], 404);
        }

        return response()->json($detallePedido);
    }

    // Crear un nuevo detalle para un pedido
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido_id' => 'required|integer|exists:pedidos,id',  // Validación del ID del pedido
            'producto_id' => 'required|integer|exists:productos,id',  // Validación del ID del producto
            'cantidad' => 'required|integer|min:1',  // Validación de la cantidad
            'precio' => 'required|numeric',  // Validación del precio
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Crear el detalle del pedido
        $detallePedido = DetallePedido::create([
            'pedido_id' => $request->pedido_id,
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad,
            'precio' => $request->precio,
        ]);

        return response()->json($detallePedido, 201);
    }

    // Eliminar un detalle de pedido
    public function destroy($id)
    {
        $detallePedido = DetallePedido::find($id);

        if (!$detallePedido) {
            return response()->json(['message' => 'Detalle de pedido no encontrado'], 404);
        }

        $detallePedido->delete();

        return response()->json(['message' => 'Detalle de pedido eliminado']);
    }
}
