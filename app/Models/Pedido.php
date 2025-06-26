<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    // Define el nombre de la tabla si no sigue la convención de Laravel (plural de la clase)
    protected $table = 'pedidos';

    // Define los campos que pueden ser asignados masivamente
    protected $fillable = [
        'usuario_id',
        'direccion',
        'estado',
        'total',
        'fecha',
    ];

    // Define los tipos de datos para las columnas (casting)
    protected $casts = [
        'fecha' => 'datetime', // Asegura que 'fecha' se maneje como un objeto DateTime
        'total' => 'float',    // Asegura que 'total' se maneje como un float
    ];

    /**
     * Relación con el Usuario que realizó el pedido.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con los Items del Pedido.
     */
    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    /**
     * Relación con los Pagos asociados a este pedido.
     */
    public function pago() // <--- Asegúrate de que esta relación sea hasOne
    {
        return $this->hasOne(Pago::class); // Un pedido tiene un pago
    }

    /**
     * Relación con el Envío asociado a este pedido.
     */
    public function envio()
    {
        // Asumiendo que un pedido tiene un solo envío (o muchos, depende de tu lógica de negocio)
        // Si es uno a uno (un pedido tiene un envío), usa hasOne
        return $this->hasOne(Envio::class, 'pedido_id');
        // Si un pedido puede tener varios envíos (por ejemplo, entregas parciales), usa hasMany
        // return $this->hasMany(Envio::class, 'pedido_id');
    }

    // Puedes añadir métodos personalizados para calcular cosas, por ejemplo:
    /**
     * Calcula el subtotal del pedido sumando los precios de los items.
     * Esto sería útil si el 'total' en la tabla 'pedidos' es el total final
     * y necesitas el subtotal de los productos antes de impuestos/descuentos generales.
     */
    public function calculateSubtotal()
    {
        return $this->items->sum(function ($item) {
            return $item->cantidad * $item->precio_unit;
        });
    }



    public function pagos()
    {
        return $this->hasMany(\App\Models\Pago::class);
    }

    public function ultimoPago()
    {
        return $this->hasOne(\App\Models\Pago::class)->latest();
    }
}
