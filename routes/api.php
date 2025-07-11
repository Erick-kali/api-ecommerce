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


Route::apiResource('usuarios', UsuarioController::class);
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::get('departamentos', [UbicacionController::class, 'getDepartamentos']);
Route::get('provincias/{departamentoId}', [UbicacionController::class, 'getProvincias']);
Route::get('distritos/{provinciaId}', [UbicacionController::class, 'getDistritos']);
Route::put('usuarios/{id}/rol', [AuthController::class, 'updateRole']);
Route::apiResource('categorias', CategoriaApiController::class);

Route::apiResource('productos-promocion', ProductoPromocionController::class);
Route::apiResource('promociones', PromocionController::class);
Route::apiResource('productos', ProductoController::class);
Route::get('carrito/{usuario_id}', [CarritoController::class, 'index']);  
Route::post('carrito', [CarritoController::class, 'store']); 
Route::put('carrito/{id}', [CarritoController::class, 'update']); 
Route::delete('carrito/{id}', [CarritoController::class, 'destroy']);

Route::get('pedidos/{usuario_id}', [PedidoController::class, 'index']);  
Route::post('pedidos', [PedidoController::class, 'store']);  
Route::put('pedidos/{id}', [PedidoController::class, 'update']);  
Route::delete('pedidos/{id}', [PedidoController::class, 'destroy']);  

Route::get('detalle_pedido/{pedido_id}', [DetallePedidoController::class, 'index']);  
Route::post('detalle_pedido', [DetallePedidoController::class, 'store']); 
Route::delete('detalle_pedido/{id}', [DetallePedidoController::class, 'destroy']); 

Route::get('pagos/{pedido_id}', [PagoController::class, 'index']);  
Route::post('pagos', [PagoController::class, 'store']);
Route::put('pagos/{id}', [PagoController::class, 'update']);  
Route::delete('pagos/{id}', [PagoController::class, 'destroy']);  

Route::get('comentarios', [ComentariosController::class, 'index']);
Route::get('comentarios/{id}', [ComentariosController::class, 'show']);
Route::post('comentarios', [ComentariosController::class, 'store']);
Route::put('comentarios/{id}', [ComentariosController::class, 'update']);
Route::delete('comentarios/{id}', [ComentariosController::class, 'destroy']);

Route::resource('imagenes-promociones', ImagenesPromocionController::class);

//{
    //"rol": "admin"
//}
