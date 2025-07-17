<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Hola, {{ $pedido->usuario->nombre ?? 'cliente' }}!</h1>
    <p>Tu pago por <strong>S/. {{ number_format($pedido->total, 2) }}</strong> se ha procesado correctamente.</p>
    <p>Adjuntamos la factura correspondiente a tu pedido #{{ $pedido->id }}. Gracias por confiar en nosotros.</p>
    <p>Saludos cordiales,<br>El equipo de <strong>NightFood</strong>.</p>
</body>
</html>
