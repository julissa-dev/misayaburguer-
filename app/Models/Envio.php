<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    use HasFactory;

    // Define el nombre de la tabla si no sigue la convención de Laravel
    protected $table = 'envios';

    // Define los campos que pueden ser asignados masivamente
    protected $fillable = [
        'pedido_id',
        'repartidor_id',
        'estado',
        'actualizado_en',
    ];

    // Define los tipos de datos para las columnas (casting)
    protected $casts = [
        'actualizado_en' => 'datetime', // Asegura que 'actualizado_en' se maneje como un objeto DateTime
        'estado' => 'string', // Puedes castearlo a string o a un enum si lo deseas en Laravel 10+
    ];

    /**
     * Relación con el Pedido al que pertenece este envío.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Relación con el Repartidor asignado a este envío (si tienes una tabla de repartidores).
     *
     * Asumo que 'repartidor_id' hace referencia a una tabla de usuarios o una tabla específica de repartidores.
     * Si 'repartidor_id' se refiere a la tabla 'usuarios' (por ejemplo, con un rol de 'repartidor'),
     * entonces la relación sería con el modelo 'Usuario'.
     * Si tienes una tabla 'repartidores' separada, crea un modelo 'Repartidor'.
     */
    public function repartidor()
    {
        // OPCIÓN 1: Si los repartidores son usuarios con un rol específico
        return $this->belongsTo(Usuario::class, 'repartidor_id');

        // OPCIÓN 2: Si tienes un modelo 'Repartidor' separado
        // return $this->belongsTo(Repartidor::class, 'repartidor_id');
    }
}