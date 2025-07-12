<?php
// app/Models/Promocion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'descuento',
        'fecha_inicio',
        'fecha_fin',
        'producto_id',
        'estado',
        'imagen_url'
    ];

    // Relación de muchos a uno con el producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
