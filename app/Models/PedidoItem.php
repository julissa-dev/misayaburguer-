<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    // Define los campos que pueden ser asignados masivamente
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'promocion_id', // ¡Añade esta línea!
        'cantidad',
        'precio_unit',
    ];

    /**
     * Relación con el Pedido.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Relación con el Producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Relación con la Promoción (opcional).
     * Esto te permitirá acceder a los detalles de la promoción si el item proviene de una.
     */
    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }
}