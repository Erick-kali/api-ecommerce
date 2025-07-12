<?php
// app/Http/Controllers/API/PagoController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Validator;

class PagoController extends Controller
{
    // Obtener pagos de un pedido
    public function index($pedido_id)
    {
        $pago = Pago::where('pedido_id', $pedido_id)->first();

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        return response()->json($pago);
    }

    // Crear un nuevo pago
    public function store(Request $request)
    {
        // Validación de los datos de entrada
        $validated = $request->validate([
            'pedido_id' => 'required|integer',
            'metodo_pago' => 'required|in:tarjeta,efectivo,transferencia',
            'estado_pago' => 'nullable|in:pendiente,completado,fallido',
        ]);

        // Crear un nuevo registro de pago
        $pago = Pago::create([
            'pedido_id' => $validated['pedido_id'],
            'metodo_pago' => $validated['metodo_pago'],
            'estado_pago' => $validated['estado_pago'] ?? 'pendiente', // si el estado no se pasa, por defecto es 'pendiente'
        ]);

        return response()->json($pago, 201);
    }

    // Actualizar el estado del pago
    public function update(Request $request, $id)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'estado_pago' => 'required|in:pendiente,completado,fallido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        // Actualizar el estado del pago
        $pago->estado_pago = $request->estado_pago;
        $pago->save();

        return response()->json($pago);
    }
}
