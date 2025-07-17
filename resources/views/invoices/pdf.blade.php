<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 120px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .footer { margin-top: 30px; text-align: center; font-size: 0.8em; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <img class="logo" src="{{ public_path('images/logo.png') }}" alt="NightFood">
        <h2>Factura #{{ $pedido->id }}</h2>
        <p>Fecha: {{ $pedido->fecha_pedido }}</p>
    </div>

    <table>
        <thead>
            <tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
        @foreach($pedido->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->nombre }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td>S/. {{ number_format($detalle->producto->precio, 2) }}</td>
                <td>S/. {{ number_format($detalle->cantidad * $detalle->producto->precio, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right">Total:</th>
                <th>S/. {{ number_format($pedido->total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Gracias por tu compra.</p>
        <p>— Firma digital de NightFood —</p>
    </div>
</body>
</html>
