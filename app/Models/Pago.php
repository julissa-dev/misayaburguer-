<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    // Define el nombre de la tabla si no sigue la convención de Laravel
    protected $table = 'pagos';

    // Define los campos que pueden ser asignados masivamente
    protected $fillable = [
        'pedido_id',
        'metodo',
        'estado',
        'fecha',
        'referencia',
    ];

    // Define los tipos de datos para las columnas (casting)
    protected $casts = [
        'fecha' => 'datetime', // Asegura que 'fecha' se maneje como un objeto DateTime
    ];

    /**
     * Relación con el Pedido al que pertenece este pago.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}