<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{

    use HasFactory;

    protected $table = 'productos'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'imagen_url',
        'categoria_id', // ¡Importante!
        'disponible'
    ];

    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtolower($value), // Forma corta de Closure
            get: fn ($value) => ucwords($value),   // Forma corta de Closure
        );
    }

    // Define la relación "muchos a uno" con Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
