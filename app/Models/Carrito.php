<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Es buena práctica incluirlo

class Carrito extends Model
{
    use HasFactory; // Usado para factories, si los necesitas

    protected $table = 'carritos'; // Nombre de la tabla en la base de datos

    // Agrega las propiedades fillable si usas asignación masiva
    protected $fillable = [
        'usuario_id', // ¡Importante! Asegúrate de que esta columna exista en tu tabla carritos
        // ... otros campos como 'estado', 'total_items', etc.
    ];

    /**
     * Define la relación: un Carrito pertenece a un Usuario.
     * Laravel asume que la clave foránea es 'usuario_id' en la tabla 'carritos'.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class); // Asumiendo que tu modelo de usuario es 'User'
    }

    /**
     * Define la relación: un Carrito tiene muchos CarritoItem.
     */
    public function items()
    {
        return $this->hasMany(CarritoItem::class);
    }

    public function promociones()
    {
        return $this->hasMany(CarritoPromocion::class, 'carrito_id');
    }
}
