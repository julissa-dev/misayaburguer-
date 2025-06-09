<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
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

        // 4. Obtén todos los productos. Si tu vista los necesita para mostrar un catálogo, por ejemplo.
        $productos = Producto::all();

        // 5. Retorna la vista 'home' pasando todas las variables necesarias.
        // La función 'compact()' es una forma concisa de pasar variables a la vista.
        return view('home', compact('contador', 'productos', 'carritoItems', 'carrito', 'totalPrice'));
    }

    public function menu()
    {
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

        $categorias = Producto::select('categoria')->distinct()->get()->pluck('categoria');

        // 4. Obtén todos los productos paginados y que estén disponibles.
        $productos = Producto::where('disponible', 1) // O puedes usar ->where('disponible', '>', 0)
            ->paginate(10);
        return view('menu', compact('contador', 'productos', 'carritoItems', 'carrito', 'totalPrice', 'categorias'));
    }

    public function perfil()
    {

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

        return view('perfil', compact('contador',  'carritoItems', 'carrito', 'totalPrice'));
    }

    public function buscarProductos(Request $request)
    {
        $query = $request->input('query');
        $productos = [];

        if ($query) {
            // Asegura la búsqueda insensible a mayúsculas/minúsculas
            // y verifica que la cantidad 'disponible' sea mayor que 0
            $productos = Producto::whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($query) . '%'])
                ->where('disponible', 1) // Busca donde disponible sea exactamente 1
                ->limit(10)
                ->get();

            $productos->map(function ($producto) {
                // Asegúrate de que el campo 'imagen_url' en tu BD contenga solo el nombre del archivo (ej. 'burger1.jpg')
                // asset() genera la URL pública para archivos en public/, y 'storage/' se mapea al enlace simbólico.
                $producto->imagen_url = asset('storage/img/productos/' . $producto->imagen_url);
                return $producto;
            });
        }

        // Devolver los productos como JSON
        return response()->json($productos);
    }
}
