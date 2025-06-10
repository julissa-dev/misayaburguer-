<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductoController extends Controller
{
    public function show(Producto $producto)
    {
        $producto->load('categoria');

        $carrito = null; // Inicializa el carrito a null por si no hay un usuario autenticado o un carrito existente
        $carritoItems = collect(); // Inicializa carritoItems como una colección vacía de Laravel
        $contador = 0; // Inicializa el contador de ítems
        $totalPrice = 0;

        // 1. Verificar si el usuario está autenticado y, si lo está, intentar encontrar su carrito
        if (Auth::check()) {
            // Busca el carrito asociado al ID del usuario autenticado
            $carrito = Carrito::where('usuario_id', Auth::id())->first();

            // 2. Si se encuentra un carrito, carga sus ítems.
            // Es crucial usar 'with('producto')' para cargar los detalles del producto
            // junto con cada CarritoItem. Esto evita el problema de "N+1 queries",
            // donde Laravel haría una consulta a la base de datos por cada producto en el carrito.
            if ($carrito) {
                $carritoItems = CarritoItem::with('producto') // Carga la relación 'producto'
                    ->where('carrito_id', $carrito->id)
                    ->get();

                // 3. Calcula el contador sumando las cantidades de los ítems del carrito.
                // El método 'sum()' de las colecciones de Laravel es muy útil para esto.
                $contador = $carritoItems->sum('cantidad');

                foreach ($carritoItems as $item) {
                    if ($item->producto) {
                        $totalPrice += $item->producto->precio * $item->cantidad;
                    }
                }
            }
        }
        $productos = Producto::all();

        // --- LÓGICA PARA PRODUCTOS RELACIONADOS ---
        $productosRelacionados = collect(); // Inicializa como colección vacía

        if ($producto->categoria) { // Asegúrate de que el producto tiene una categoría asignada
            $productosRelacionados = Producto::where('categoria_id', $producto->categoria->id) // Mismos de la misma categoría
                ->where('id', '!=', $producto->id) // Excluir el producto actual
                ->inRandomOrder() // Opcional: mostrar en orden aleatorio
                ->limit(4) // Mostrar un número limitado de productos (e.g., 4)
                ->get();
        }
        // --- FIN LÓGICA PRODUCTOS RELACIONADOS ---

        

        return view('productos.show', compact('contador', 'productos', 'carritoItems', 'carrito', 'totalPrice', 'producto', 'productosRelacionados'));
    }
}
