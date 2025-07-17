<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class PedidoController extends Controller
{
    /**
     * Obtener todos los pedidos de un usuario específico
     */
    public function index($usuario_id)
    {
        try {
            // Validar que el usuario_id sea un número
            if (!is_numeric($usuario_id)) {
                return response()->json(['message' => 'ID de usuario no válido'], 400);
            }

            // Recuperar todos los pedidos del usuario ordenados por fecha más reciente
            $pedidos = Pedido::where('usuario_id', $usuario_id)
                            ->orderBy('created_at', 'desc')
                            ->get();

            if ($pedidos->isEmpty()) {
                return response()->json(['message' => 'No se encontraron pedidos para este usuario.'], 404);
            }

            return response()->json($pedidos, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener los pedidos', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener los pedidos de un usuario filtrados por estado
     */
    public function indexByStatus($usuario_id, $estado)
    {
        try {
            // Validar que el usuario_id sea un número
            if (!is_numeric($usuario_id)) {
                return response()->json(['message' => 'ID de usuario no válido'], 400);
            }

            // Validar que el estado sea uno de los valores válidos
            $validStates = ['pendiente', 'enviado', 'entregado', 'cancelado'];

            if (!in_array($estado, $validStates)) {
                return response()->json(['message' => 'Estado no válido. Estados válidos: ' . implode(', ', $validStates)], 400);
            }

            // Obtener los pedidos filtrados por el estado
            $pedidos = Pedido::where('usuario_id', $usuario_id)
                            ->where('estado', $estado)
                            ->orderBy('created_at', 'desc')
                            ->get();

            if ($pedidos->isEmpty()) {
                return response()->json(['message' => "No se encontraron pedidos con estado: $estado"], 404);
            }

            return response()->json($pedidos, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al filtrar los pedidos', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener todos los pedidos de todos los usuarios (para admin)
     */
    public function getAllPedidos()
    {
        try {
            $pedidos = Pedido::with('usuario') // Incluir información del usuario si tienes la relación
                            ->orderBy('created_at', 'desc')
                            ->get();

            if ($pedidos->isEmpty()) {
                return response()->json(['message' => 'No se encontraron pedidos.'], 404);
            }

            return response()->json($pedidos, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener todos los pedidos', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener todos los pedidos por estado específico (para admin)
     */
    public function indexByStatusOnly($estado)
    {
        try {
            $validStates = ['pendiente', 'enviado', 'entregado', 'cancelado'];

            if (!in_array($estado, $validStates)) {
                return response()->json(['message' => 'Estado no válido. Estados válidos: ' . implode(', ', $validStates)], 400);
            }

            // Obtener todos los pedidos filtrados por estado
            $pedidos = Pedido::where('estado', $estado)
                            ->with('usuario') // Incluir información del usuario si tienes la relación
                            ->orderBy('created_at', 'desc')
                            ->get();

            if ($pedidos->isEmpty()) {
                return response()->json(['message' => "No se encontraron pedidos con estado: $estado"], 404);
            }

            return response()->json($pedidos, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al filtrar pedidos por estado', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener un pedido específico por ID
     */
    public function show($id)
    {
        try {
            if (!is_numeric($id)) {
                return response()->json(['message' => 'ID de pedido no válido'], 400);
            }

            $pedido = Pedido::with('usuario')->find($id);

            if (!$pedido) {
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }

            return response()->json($pedido, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener el pedido', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear un nuevo pedido
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'usuario_id' => 'required|integer|exists:usuarios,id',
                'total' => 'required|numeric|min:0',
                'estado' => 'nullable|in:pendiente,enviado,entregado,cancelado',
            ], [
                'usuario_id.required' => 'El ID del usuario es requerido',
                'usuario_id.exists' => 'El usuario no existe',
                'total.required' => 'El total es requerido',
                'total.numeric' => 'El total debe ser un número',
                'total.min' => 'El total debe ser mayor o igual a 0',
                'estado.in' => 'El estado debe ser: pendiente, enviado, entregado o cancelado',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pedido = Pedido::create([
                'usuario_id' => $request->usuario_id,
                'total' => $request->total,
                'estado' => $request->estado ?? 'pendiente',
                'fecha_pedido' => now(),
            ]);

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'pedido' => $pedido
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al crear el pedido',
                'error' => 'Error en la base de datos'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un pedido existente
     */
    public function update(Request $request, $id)
    {
        try {
            if (!is_numeric($id)) {
                return response()->json(['message' => 'ID de pedido no válido'], 400);
            }

            $pedido = Pedido::find($id);

            if (!$pedido) {
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }

            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'estado' => 'nullable|in:pendiente,enviado,entregado,cancelado',
                'total' => 'nullable|numeric|min:0',
                'usuario_id' => 'nullable|integer|exists:usuarios,id',
            ], [
                'estado.in' => 'El estado debe ser: pendiente, enviado, entregado o cancelado',
                'total.numeric' => 'El total debe ser un número',
                'total.min' => 'El total debe ser mayor o igual a 0',
                'usuario_id.exists' => 'El usuario no existe',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar solo los campos que se enviaron
            if ($request->has('estado')) {
                $pedido->estado = $request->estado;
            }

            if ($request->has('total')) {
                $pedido->total = $request->total;
            }

            if ($request->has('usuario_id')) {
                $pedido->usuario_id = $request->usuario_id;
            }

            $pedido->save();

            return response()->json([
                'message' => 'Pedido actualizado exitosamente',
                'pedido' => $pedido
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al actualizar el pedido',
                'error' => 'Error en la base de datos'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un pedido
     */
    public function destroy($id)
    {
        try {
            if (!is_numeric($id)) {
                return response()->json(['message' => 'ID de pedido no válido'], 400);
            }

            $pedido = Pedido::find($id);

            if (!$pedido) {
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }

            // Opcional: Verificar si el pedido puede ser eliminado
            if ($pedido->estado === 'entregado') {
                return response()->json(['message' => 'No se puede eliminar un pedido entregado'], 400);
            }

            $pedido->delete();

            return response()->json([
                'message' => 'Pedido eliminado exitosamente'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al eliminar el pedido',
                'error' => 'Error en la base de datos'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar un pedido (cambiar estado a cancelado)
     */
    public function cancel($id)
    {
        try {
            if (!is_numeric($id)) {
                return response()->json(['message' => 'ID de pedido no válido'], 400);
            }

            $pedido = Pedido::find($id);

            if (!$pedido) {
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }

            // Verificar si el pedido puede ser cancelado
            if ($pedido->estado === 'entregado') {
                return response()->json(['message' => 'No se puede cancelar un pedido entregado'], 400);
            }

            if ($pedido->estado === 'cancelado') {
                return response()->json(['message' => 'El pedido ya está cancelado'], 400);
            }

            $pedido->estado = 'cancelado';
            $pedido->save();

            return response()->json([
                'message' => 'Pedido cancelado exitosamente',
                'pedido' => $pedido
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al cancelar el pedido',
                'error' => 'Error en la base de datos'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de pedidos
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_pedidos' => Pedido::count(),
                'pendientes' => Pedido::where('estado', 'pendiente')->count(),
                'enviados' => Pedido::where('estado', 'enviado')->count(),
                'entregados' => Pedido::where('estado', 'entregado')->count(),
                'cancelados' => Pedido::where('estado', 'cancelado')->count(),
                'total_ventas' => Pedido::where('estado', 'entregado')->sum('total'),
            ];

            return response()->json($stats, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}