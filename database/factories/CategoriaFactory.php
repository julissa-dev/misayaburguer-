<?php

namespace Database\Factories;

use App\Models\Categoria; // Asegúrate de importar el modelo
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Categoria::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Define algunas categorías comunes para que los productos puedan referenciarlas
        $categorias = [
            'Hamburguesas',
            'Bebidas',
            'Complementos',// Puedes añadir más según tu negocio
        ];

        // Faker puede generar un nombre aleatorio de los predefinidos
        $nombreCategoria = $this->faker->unique()->randomElement($categorias);

        // Define algunas imágenes de icono por defecto si las tienes
        // Asegúrate de que estos archivos de imagen existan en public/storage/img/categorias/
        $imagenesIcono = [
            'hamburguesa_icon.png',
            'bebida_icon.jpg',
            'complemento_icon.png',
            // Añade los nombres de tus archivos de imagen reales
        ];

        // Mapea el nombre de la categoría a una imagen de icono específica (opcional, puedes randomizar más)
        $imagenIcono = 'default_icon.png'; // Imagen por defecto si no hay una específica

        if (str_contains(strtolower($nombreCategoria), 'hamburguesa')) {
            $imagenIcono = 'hamburguesa_icon.png';
        } elseif (str_contains(strtolower($nombreCategoria), 'bebida')) {
            $imagenIcono = 'bebida_icon.png';
        } elseif (str_contains(strtolower($nombreCategoria), 'complemento')) {
            $imagenIcono = 'complemento_icon.png';
        } else {
             // Si la categoría no coincide con ninguna predefinida, elige una al azar de las que existen
            $imagenIcono = $this->faker->randomElement($imagenesIcono);
        }


        return [
            'nombre' => $nombreCategoria,
            'imagen_icono' => $imagenIcono, // Asegúrate de tener estas imágenes en public/storage/img/categorias
        ];
    }
}