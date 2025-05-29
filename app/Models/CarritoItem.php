<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Es buena práctica incluirlo

class CarritoItem extends Model
{
    use HasFactory; // Usado para factories, si los necesitas

    protected $table = 'carrito_items'; // Nombre de la tabla en la base de datos

    // Agrega las propiedades fillable si usas asignación masiva (por ejemplo, con CarritoItem::create([...]))
    protected $fillable = [
        'carrito_id', // ¡Importante! Asegúrate de que esta columna exista en tu tabla carrito_items
        'producto_id',
        'cantidad',
        // ... otros campos que puedas tener
    ];

    /**
     * Define la relación: un CarritoItem pertenece a un Carrito.
     * Laravel asume que la clave foránea es 'carrito_id' en la tabla 'carrito_items'.
     */
    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    /**
     * Define la relación: un CarritoItem pertenece a un Producto.
     * Laravel asume que la clave foránea es 'producto_id' en la tabla 'carrito_items'.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}