<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'imagen_icono']; // Campos que se pueden asignar masivamente

    // Define la relación "uno a muchos" con Producto
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}