<?php

namespace Database\Factories;

use App\Models\Producto; // Asegúrate de importar el modelo
use App\Models\Categoria; // Necesitamos el modelo Categoria para la clave foránea
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Define algunas imágenes de producto por defecto para que no fallen
        // Asegúrate de que estos archivos de imagen existan en public/storage/img/productos/
        $imagenesProducto = [
            'burger1.jpg',
            'burger2.jpg',
            'soda.jpg',
            'fries.jpg',
            'cake.jpg',
            // Añade más nombres de tus archivos de imagen reales
        ];

        return [
            'nombre' => $this->faker->words(2, true) . ' ' . $this->faker->randomElement(['Especial', 'Deluxe', 'Clásico', 'Premium']),
            'descripcion' => $this->faker->sentence(8),
            'precio' => $this->faker->randomFloat(2, 5, 100), // Precio entre 5.00 y 25.00
            'imagen_url' => $this->faker->randomElement($imagenesProducto), // Elige una imagen aleatoria
            'disponible' => $this->faker->boolean(80), // 80% de probabilidad de ser true (disponible)
            // Relaciona con una Categoria existente. Si no hay categorías, créala automáticamente.
            // Esto es crucial para la clave foránea.
            'categoria_id' => Categoria::inRandomOrder()->first()->id,
        ];
    }
}