<?php
// app/Models/ProductoPromocion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPromocion extends Model
{
    use HasFactory;

    protected $table = 'productos_promocion';

    protected $fillable = [
        'promocion_id',
        'producto_id',
    ];

    // Relación con la tabla de Promocion (cada producto promocional pertenece a una promoción)
    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }

    // Relación con la tabla de Producto (cada producto promocional pertenece a un producto)
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
