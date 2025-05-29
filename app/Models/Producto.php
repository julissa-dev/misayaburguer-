<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos'; // Nombre de la tabla en la base de datos

    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtolower($value), // Forma corta de Closure
            get: fn ($value) => ucwords($value),   // Forma corta de Closure
        );
    }
}
