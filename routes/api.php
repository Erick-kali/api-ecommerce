<?php
use App\Http\Controllers\API\UbicacionController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductoController;
use App\Http\Controllers\API\CategoriaApiController;
use App\Http\Controllers\API\PromocionController;
use App\Http\Controllers\API\ProductoPromocionController;
use App\Http\Controllers\API\CarritoController;
use App\Http\Controllers\API\PedidoController;
use App\Http\Controllers\API\DetallePedidoController;
use App\Http\Controllers\API\PagoController;
use App\Http\Controllers\API\ComentariosController;
use App\Http\Controllers\API\ImagenesPromocionController;
use App\Http\Controllers\API\CheckoutController;

Route::apiResource('usuarios', UsuarioController::class);
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::get('departamentos', [UbicacionController::class, 'getDepartamentos']);
Route::get('provincias/{departamentoId}', [UbicacionController::class, 'getProvincias']);
Route::get('distritos/{provinciaId}', [UbicacionController::class, 'getDistritos']);
Route::put('usuarios/{id}/rol', [AuthController::class, 'updateRole']);
//{
    //"rol": "admin"
//}
Route::apiResource('categorias', CategoriaApiController::class);

Route::apiResource('productos-promocion', ProductoPromocionController::class);

    
Route::apiResource('productos', ProductoController::class);
Route::get('carrito/{usuario_id}', [CarritoController::class, 'index']);  
Route::post('carrito', [CarritoController::class, 'store']); 
Route::put('carrito/{id}', [CarritoController::class, 'update']); 
Route::put('carrito/{usuario_id}/todos', [CarritoController::class, 'updateAll']);  // Actualizar todos los productos de un usuario
Route::delete('carrito/{id}', [CarritoController::class, 'destroy']);
Route::delete('carrito/{usuario_id}/todos', [CarritoController::class, 'destroyAll']);  // Eliminar todos los productos de un usuario
Route::delete('carrito/todos', [CarritoController::class, 'destroyAllForAll']); // Eliminar todos los productos de todos los carritos


Route::prefix('pedidos')->group(function () {
    
    // Obtener todos los pedidos (admin)
    Route::get('/', [PedidoController::class, 'getAllPedidos']);
    
    // Obtener estadísticas
    Route::get('/stats', [PedidoController::class, 'getStats']);
    
    // Obtener pedidos por estado (admin)
    Route::get('/estado/{estado}', [PedidoController::class, 'indexByStatusOnly']);
    
    // Obtener un pedido específico
    Route::get('/{id}', [PedidoController::class, 'show']);
    
    // Crear nuevo pedido
    Route::post('/', [PedidoController::class, 'store']);
    
    // Actualizar pedido
    Route::put('/{id}', [PedidoController::class, 'update']);
    
    // Cancelar pedido
    Route::patch('/{id}/cancel', [PedidoController::class, 'cancel']);
    
    // Eliminar pedido
    Route::delete('/{id}', [PedidoController::class, 'destroy']);
    
});

// Rutas para pedidos de usuarios específicos
Route::prefix('usuarios')->group(function () {
    
    // Obtener todos los pedidos de un usuario
    Route::get('/{usuario_id}/pedidos', [PedidoController::class, 'index']);
    
    // Obtener pedidos de un usuario por estado
    Route::get('/{usuario_id}/pedidos/estado/{estado}', [PedidoController::class, 'indexByStatus']);
    
});

Route::get('pagos/{pedido_id}', [PagoController::class, 'index']);  
Route::post('pagos', [PagoController::class, 'store']);
Route::put('pagos/{id}', [PagoController::class, 'update']);  
Route::delete('pagos/{id}', [PagoController::class, 'destroy']);  

Route::post('checkout', [CheckoutController::class, 'checkout']);
Route::post('/checkout', [CheckoutController::class, 'checkout']);
Route::post('/checkout/download', [CheckoutController::class, 'checkoutAndDownload']);
Route::get('/invoice/download/{pedido_id}', [CheckoutController::class, 'downloadInvoice'])
    ->name('api.invoice.download');

Route::get('comentarios', [ComentariosController::class, 'index']);
Route::get('comentarios/{id}', [ComentariosController::class, 'show']);
Route::post('comentarios', [ComentariosController::class, 'store']);
Route::put('comentarios/{id}', [ComentariosController::class, 'update']);
Route::delete('comentarios/{id}', [ComentariosController::class, 'destroy']);

Route::resource('imagenes-promociones', ImagenesPromocionController::class);

Route::prefix('promociones')->group(function () {
    // Rutas básicas CRUD
    Route::get('/', [PromocionController::class, 'index']);
    Route::post('/', [PromocionController::class, 'store']);
    Route::get('/{id}', [PromocionController::class, 'show']);
    Route::put('/{id}', [PromocionController::class, 'update']);
    Route::delete('/{id}', [PromocionController::class, 'destroy']);
    
    // Rutas para filtrar por estado
    Route::get('/estado/activas', [PromocionController::class, 'activas']);
    Route::get('/estado/inactivas', [PromocionController::class, 'inactivas']);
    
    // Rutas para activar/desactivar
    Route::patch('/{id}/activar', [PromocionController::class, 'activar']);
    Route::patch('/{id}/desactivar', [PromocionController::class, 'desactivar']);
    
    // Rutas para soft deletes (opcional)
    Route::get('/papelera/eliminadas', [PromocionController::class, 'papelera']);
    Route::patch('/{id}/restaurar', [PromocionController::class, 'restore']);
    
    // Rutas para estadísticas y conteos
    Route::get('/estadisticas/completas', [PromocionController::class, 'estadisticas']);
    Route::get('/estadisticas/conteo', [PromocionController::class, 'conteo']);
});
