<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'usuario_id',
        'total',
        'estado',
        'fecha_pedido'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'pedido_id');
    }
}
