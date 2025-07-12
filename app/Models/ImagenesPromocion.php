<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenesPromocion extends Model
{
    use HasFactory;

    protected $table = 'imagenes_promociones'; // Asegúrate de que el nombre de la tabla sea correcto

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'promocion_id',
        'imagen_url',
    ];

    // Relación con el modelo Promocion
    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }
}
