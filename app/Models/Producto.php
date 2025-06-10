<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;       // Importa el trait HasSlug
use Spatie\Sluggable\SlugOptions;   // Importa la clase SlugOptions

class Producto extends Model
{

    use HasFactory, HasSlug; // Agrega el trait HasSlug aquí

    protected $table = 'productos'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'nombre',
        'slug', // ¡IMPORTANTE: Añade 'slug' a la lista de fillable!
        'descripcion',
        'precio',
        'imagen_url',
        'categoria_id',
        'disponible'
    ];

    // Este mutador y accesor para 'nombre' está bien y puede coexistir con el slugging.
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtolower($value), // Forma corta de Closure
            get: fn($value) => ucwords($value),   // Forma corta de Closure
        );
    }

    /**
     * Get the options for generating the slug.
     * Este método es requerido por el trait HasSlug de spatie/laravel-sluggable.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('nombre') // Genera el slug desde la columna 'nombre'
            ->saveSlugsTo('slug')        // Guarda el slug en la columna 'slug'
            ->doNotGenerateSlugsOnUpdate(); // Opcional: no regenerar el slug en cada actualización
        // Si quieres que el slug se actualice cuando cambia el nombre, quita esta línea.
    }

    /**
     * Get the route key for the model.
     * Este método le dice a Laravel que use 'slug' en lugar de 'id' para la resolución de rutas implícita.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Define la relación "muchos a uno" con Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
