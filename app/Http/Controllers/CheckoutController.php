<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF; // alias de barryvdh/laravel-dompdf
use App\Mail\InvoiceMail;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'usuario_id'   => 'required|exists:usuarios,id',
            'metodo_pago'  => 'required|in:tarjeta,efectivo,transferencia',
            // ... otros datos de pago si aplica
        ]);

        $usuario = Usuario::find($request->usuario_id);
        $items   = Carrito::with('producto')
                    ->where('usuario_id', $usuario->id)
                    ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 422);
        }

        // Calcular total
        $total = $items->sum(fn($item) => $item->cantidad * $item->producto->precio);

        DB::beginTransaction();
        try {
            // 1) Crear Pedido
            $pedido = Pedido::create([
                'usuario_id'   => $usuario->id,
                'total'        => $total,
                'estado'       => 'pendiente', // o 'cancelado' si lo deseas
                'fecha_pedido' => now(),
            ]);

            // 2) Registrar Pago
            $pago = Pago::create([
                'pedido_id'   => $pedido->id,
                'metodo_pago' => $request->metodo_pago,
                'estado_pago' => 'completado',
            ]);

            // 3) Vaciar Carrito
            Carrito::where('usuario_id', $usuario->id)->delete();

            // 4) Generar PDF de factura
            $pdf = PDF::loadView('invoices.pdf', [
                'pedido' => $pedido->load('detalles.producto', 'usuario')
            ]);

            // 5) Enviar correo o devolver enlace
            if ($usuario->email) {
                Mail::to($usuario->email)
                    ->send(new InvoiceMail($pedido, $pdf->output()));
                $message = 'Pago procesado y factura enviada a tu correo.';
            } else {
                // Guardamos el PDF en storage y creamos un enlace temporal
                $filename = "factura_{$pedido->id}.pdf";
                \Storage::put("invoices/{$filename}", $pdf->output());
                $url = \Storage::url("invoices/{$filename}");
                $message = 'Pago procesado. No se encontró email. Descarga tu factura aquí: ' . url($url);
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'pedido_id' => $pedido->id,
                'total'   => $total,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
