<?php

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
        // Recuperar todos los pedidos del usuario, sin importar el estado
        $pedidos = Pedido::where('usuario_id', $usuario_id)->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron pedidos para este usuario.'], 404);
        }

        return response()->json($pedidos);
    }

    // Obtener los pedidos de un usuario filtrados por estado
    public function indexByStatus($usuario_id, $estado)
    {
        // Validar que el estado sea uno de los valores válidos
        $validStates = ['pendiente', 'enviado', 'entregado', 'cancelado'];

        if (!in_array($estado, $validStates)) {
            return response()->json(['message' => 'Estado no válido'], 400);
        }

        // Obtener los pedidos filtrados por el estado
        $pedidos = Pedido::where('usuario_id', $usuario_id)
                        ->where('estado', $estado)  // Filtrar por estado
                        ->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron pedidos con este estado.'], 404);
        }

        return response()->json($pedidos);  // Retornar los pedidos filtrados por estado
    }
    public function getAllPedidos()
    {
        $pedidos = Pedido::all();  // Obtener todos los pedidos de todos los usuarios

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron pedidos.'], 404);
        }

        return response()->json($pedidos);
    }

    public function indexByStatusOnly($estado)
    {
        $validStates = ['pendiente', 'enviado', 'entregado', 'cancelado'];

        if (!in_array($estado, $validStates)) {
            return response()->json(['message' => 'Estado no válido'], 400);  // Error si el estado no es válido
        }

        // Obtener todos los pedidos filtrados por estado
        $pedidos = Pedido::where('estado', $estado)->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron pedidos con este estado.'], 404);  // Si no hay pedidos con ese estado
        }

        return response()->json($pedidos);  // Retorna los pedidos con el estado solicitado
    }


    // Crear un nuevo pedido
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'total' => 'required|numeric',
            'estado' => 'nullable|in:pendiente,enviado,entregado,cancelado',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pedido = Pedido::create([
            'usuario_id' => $request->usuario_id,
            'total' => $request->total,
            'estado' => $request->estado ?? 'pendiente',
            'fecha_pedido' => now(),
        ]);

        return response()->json($pedido, 201);  // Pedido creado con éxito
    }

    // Actualizar el estado de un pedido
    public function update(Request $request, $id)
    {
        // Validamos el estado y el total (si lo proporcionan)
        $validator = Validator::make($request->all(), [
            'estado' => 'nullable|in:pendiente,enviado,entregado,cancelado',
            'total' => 'nullable|numeric',  // Permitir el campo total de forma opcional
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        // Actualizar el estado si lo pasaron en la solicitud
        if ($request->has('estado')) {
            $pedido->estado = $request->estado;
        }

        // Actualizar el precio (total) si lo pasaron en la solicitud
        if ($request->has('total')) {
            $pedido->total = $request->total;
        }

        // Guardar el pedido con los nuevos valores
        $pedido->save();

        return response()->json($pedido);  // Pedido actualizado con éxito
    }

    // Eliminar un pedido
    public function destroy($id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $pedido->delete();

        return response()->json(['message' => 'Pedido eliminado']);  // Confirmación de eliminación
    }
}
