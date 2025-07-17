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
use Illuminate\Support\Facades\Storage;
use PDF; // alias de barryvdh/laravel-dompdf
use App\Mail\InvoiceMail;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'metodo_pago' => 'required|in:tarjeta,efectivo,transferencia',
            'factura_opcion' => 'nullable|in:correo,descargar'
        ]);

        $usuario = Usuario::find($request->usuario_id);
        $items = Carrito::with('producto')->where('usuario_id', $usuario->id)->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 422);
        }

        $total = $items->sum(fn($item) => $item->cantidad * $item->producto->precio);

        DB::beginTransaction();

        try {
            $pedido = Pedido::create([
                'usuario_id' => $usuario->id,
                'total' => $total,
                'estado' => 'pendiente',
                'fecha_pedido' => now(),
            ]);

            Pago::create([
                'pedido_id' => $pedido->id,
                'metodo_pago' => $request->metodo_pago,
                'estado_pago' => 'completado',
            ]);

            Carrito::where('usuario_id', $usuario->id)->delete();

            $opcion = $request->input('factura_opcion', 'descargar');

            if ($opcion === 'correo' && $usuario->email) {
                // Generar y enviar factura por correo
                $pdf = PDF::loadView('invoices.pdf', [
                    'pedido' => $pedido->load('detalles.producto', 'usuario')
                ]);
                
                Mail::to($usuario->email)->send(new InvoiceMail($pedido, $pdf->output()));
                $message = 'Pago procesado y factura enviada a tu correo.';
                $download_url = null;
            } else {
                // Generar URL de descarga
                $message = 'Pago procesado. Usa el enlace para descargar tu factura.';
                $download_url = route('api.invoice.download', ['pedido_id' => $pedido->id]);
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'pedido_id' => $pedido->id,
                'total' => $total,
                'factura_url' => $download_url
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar pago y descargar factura inmediatamente
     */
    public function checkoutAndDownload(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'metodo_pago' => 'required|in:tarjeta,efectivo,transferencia'
        ]);

        $usuario = Usuario::find($request->usuario_id);
        $items = Carrito::with('producto')->where('usuario_id', $usuario->id)->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 422);
        }

        $total = $items->sum(fn($item) => $item->cantidad * $item->producto->precio);

        DB::beginTransaction();

        try {
            $pedido = Pedido::create([
                'usuario_id' => $usuario->id,
                'total' => $total,
                'estado' => 'pendiente',
                'fecha_pedido' => now(),
            ]);

            Pago::create([
                'pedido_id' => $pedido->id,
                'metodo_pago' => $request->metodo_pago,
                'estado_pago' => 'completado',
            ]);

            Carrito::where('usuario_id', $usuario->id)->delete();

            // Generar factura PDF
            $pdf = PDF::loadView('invoices.pdf', [
                'pedido' => $pedido->load('detalles.producto', 'usuario')
            ]);

            DB::commit();

            // FORZAR descarga inmediata
            $filename = "factura_{$pedido->id}.pdf";
            $pdfOutput = $pdf->output();
            
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT',
                'Accept-Ranges' => 'bytes',
                'Content-Length' => strlen($pdfOutput)
            ];
            
            return response($pdfOutput, 200, $headers);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar factura por ID de pedido - FORZAR descarga
     */
    public function downloadInvoice($pedido_id)
    {
        try {
            $pedido = Pedido::with('detalles.producto', 'usuario')->findOrFail($pedido_id);
            
            $pdf = PDF::loadView('invoices.pdf', ['pedido' => $pedido]);
            $filename = "factura_{$pedido->id}.pdf";
            $pdfOutput = $pdf->output();
            
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT',
                'Accept-Ranges' => 'bytes',
                'Content-Length' => strlen($pdfOutput)
            ];
            
            return response($pdfOutput, 200, $headers);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al descargar la factura: ' . $e->getMessage()
            ], 500);
        }
    }
}