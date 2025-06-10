<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria; // Importa el modelo Categoria

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usa firstOrCreate para asegurar que estas categorías se creen solo si no existen
        // y para evitar errores de duplicados si la columna 'nombre' es UNIQUE.

        Categoria::firstOrCreate(
            ['nombre' => 'Hamburguesas'],
            ['imagen_icono' => 'hamburguesa_icon.png']
        );

        Categoria::firstOrCreate(
            ['nombre' => 'Bebidas'],
            ['imagen_icono' => 'bebida_icon.png']
        );

        Categoria::firstOrCreate(
            ['nombre' => 'Complementos'],
            ['imagen_icono' => 'complemento_icon.png']
        );

        Categoria::firstOrCreate(
            ['nombre' => 'Postres'], // Si quieres añadir esta, o la que necesites
            ['imagen_icono' => 'postre_icon.png']
        );

        // Puedes añadir más categorías específicas aquí si lo deseas
    }
}