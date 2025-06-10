<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Categoria;
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

    public function menu(Request $request)
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

        $categorias = Categoria::select('id', 'nombre', 'imagen_icono')->get();

        if ($request->ajax()) {
            return $this->getFilteredProducts($request);
        }

        // 4. Obtén todos los productos paginados y que estén disponibles, y carga la relación 'categoria'.
        $productos = Producto::with('categoria') // Carga la relación 'categoria' en los productos
            ->where('disponible', 1)
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

     // Método para filtrar productos (será llamado por AJAX)
    public function getFilteredProducts(Request $request)
    {
        $query = Producto::with('categoria')->where('disponible', 1);

        // Filtrar por categoría
        // Verifica si 'categoria_id' está presente y no es 'all'
        if ($request->has('categoria_id') && $request->input('categoria_id') !== 'all') {
            $query->where('categoria_id', $request->input('categoria_id'));
        }

        // Filtrar por rangos de precio
        if ($request->has('min_price') && $request->has('max_price')) {
            $minPrices = $request->input('min_price');
            $maxPrices = $request->input('max_price');

            // Asegurarse de que al menos un rango de precio esté seleccionado
            if (is_array($minPrices) && count($minPrices) > 0) {
                $query->where(function ($q) use ($minPrices, $maxPrices) {
                    foreach ($minPrices as $key => $min) {
                        $max = $maxPrices[$key];
                        $q->orWhereBetween('precio', [(float)$min, (float)$max]);
                    }
                });
            }
        }

        // Búsqueda por query (si la tienes implementada y quieres combinarla)
        if ($request->has('query')) {
            $searchQuery = $request->input('query');
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($searchQuery) . '%']);
        }

        // Paginación
        $productos = $query->paginate(10); // Puedes ajustar el número de ítems por página

        // Modificar la imagen_url para que sea una URL completa para el frontend
        $productos->getCollection()->transform(function ($producto) {
            $producto->imagen_url = asset('storage/img/productos/' . $producto->imagen_url);
            return $producto;
        });

        
        

        // Devolver los datos de productos paginados como JSON
        return response()->json(['productos' => $productos]);
    }
}
