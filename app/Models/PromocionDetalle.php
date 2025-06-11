<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromocionDetalle extends Model
{
    protected $table = 'promocion_detalle';

    protected $fillable = [
        'promocion_id',
        'producto_id',
        'cantidad',
    ];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
