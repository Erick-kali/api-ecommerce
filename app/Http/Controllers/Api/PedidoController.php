<?php
// app/Http/Controllers/API/PedidoController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Validator;

class PedidoController extends Controller
{
    // Obtener todos los pedidos de un usuario
    public function index($usuario_id)
    {
        $pedidos = Pedido::where('usuario_id', $usuario_id)->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron pedidos para este usuario.'], 404);
        }

        return response()->json($pedidos);
    }

    // Crear un nuevo pedido
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|integer|exists:usuarios,id',  // Validación de ID de usuario
            'total' => 'required|numeric',  // Validación del total del pedido
            'estado' => 'nullable|in:pendiente,enviado,entregado,cancelado',  // Validación del estado
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Crear el pedido
        $pedido = Pedido::create([
            'usuario_id' => $request->usuario_id,
            'total' => $request->total,
            'estado' => $request->estado ?? 'pendiente',  // Si no se pasa estado, se pone 'pendiente'
            'fecha_pedido' => now(),
        ]);

        return response()->json($pedido, 201);
    }

    // Actualizar el estado de un pedido
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,enviado,entregado,cancelado',  // Validación del estado
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        // Actualizar el estado del pedido
        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json($pedido);
    }

    // Eliminar un pedido
    public function destroy($id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $pedido->delete();

        return response()->json(['message' => 'Pedido eliminado']);
    }
}
