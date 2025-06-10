<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto; // Importa el modelo Producto
use App\Models\Categoria; // Importa el modelo Categoria (necesario si vas a asignar categorías manualmente)

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Opción 1: Crear productos usando el Factory (recomendado)
        // Esto creará 50 productos, y cada uno tendrá una categoría_id asignada
        // usando el CategoriaFactory (o una categoría existente si ya hay).
        Producto::factory()->count(50)->create();

        
    }
}