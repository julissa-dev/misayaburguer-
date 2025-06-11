<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarritoPromocion extends Model
{
    protected $table = 'carrito_promociones';

    protected $fillable = ['carrito_id', 'promocion_id', 'cantidad'];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    
}
