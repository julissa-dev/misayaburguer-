<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\CarritoPromocion;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $carrito = null;
        $carritoItems = collect();
        $promocionItems = collect();
        $contador = 0;
        $totalPrice = 0;

        if (Auth::check()) {
            $carrito = Carrito::where('usuario_id', Auth::id())->first();

            if ($carrito) {
                // Cargar productos en el carrito
                $carritoItems = CarritoItem::with('producto')
                    ->where('carrito_id', $carrito->id)
                    ->get();

                $contador += $carritoItems->sum('cantidad');

                foreach ($carritoItems as $item) {
                    if ($item->producto) {
                        $totalPrice += $item->producto->precio * $item->cantidad;
                    }
                }

                // Cargar promociones en el carrito, incluyendo los detalles de la promoción
                $promocionItems = CarritoPromocion::with(['promocion.detalles.producto'])
                    ->where('carrito_id', $carrito->id)
                    ->get();

                $contador += $promocionItems->sum('cantidad');

                foreach ($promocionItems as $promo) {
                    if ($promo->promocion) {
                        $totalPrice += $promo->promocion->precio_promocional * $promo->cantidad;
                    }
                }
            }
        }

        // --- Nuevos datos para las secciones dinámicas (ahora al azar) ---

        // Productos para la sección "Delicias"
        // Obtener la categoría "Hamburguesas"
        $categoriaHamburguesas = Categoria::where('nombre', 'Hamburguesas')->first();

        $deliciasOrdenadas = collect(); // Inicializar como colección vacía

        if ($categoriaHamburguesas) {
            // Se obtienen 3 productos al azar de la categoría "Hamburguesas".
            $deliciasOrdenadas = Producto::where('categoria_id', $categoriaHamburguesas->id)
                                        ->inRandomOrder()
                                        ->take(3)
                                        ->get();
        }


        // Promociones activas para el carrusel
        // Se obtienen las promociones activas en orden aleatorio.
        $promociones = Promocion::where('activa', 1)
            ->with('detalles.producto')
            ->inRandomOrder() // Obtener promociones activas al azar
            ->get();

        // Productos opcionales, por si se usan en la vista general (mantengo tu línea)
        $productos = Producto::all();


        // Retorna la vista 'home' pasando todas las variables necesarias.
        return view('home', compact(
            'contador',
            'productos', // Puede que no se use directamente si solo hay delicias y promociones
            'carritoItems',
            'carrito',
            'totalPrice',
            'promociones', // Para el carrusel de promociones
            'promocionItems', // Para items del carrito que son promociones
            'deliciasOrdenadas' // Para la sección "Delicias" (ahora productos al azar de hamburguesas)
        ));
    }

    public function menu(Request $request)
    {
        $carrito = null;
        $carritoItems = collect();
        $promocionItems = collect();
        $contador = 0;
        $totalPrice = 0;

        if (Auth::check()) {
            $carrito = Carrito::where('usuario_id', Auth::id())->first(); // 

            if ($carrito) {
                // Cargar productos en el carrito
                $carritoItems = CarritoItem::with('producto')
                    ->where('carrito_id', $carrito->id) // 
                    ->get();

                $contador += $carritoItems->sum('cantidad'); // 

                foreach ($carritoItems as $item) {
                    if ($item->producto) {
                        $totalPrice += $item->producto->precio * $item->cantidad; // 
                    }
                }

                // Cargar promociones en el carrito, incluyendo los detalles de la promoción
                // y los productos dentro de esos detalles.
                $promocionItems = CarritoPromocion::with(['promocion.detalles.producto']) // Carga anidada para optimizar 
                    ->where('carrito_id', $carrito->id) // 
                    ->get();

                $contador += $promocionItems->sum('cantidad'); // 

                foreach ($promocionItems as $promo) {
                    if ($promo->promocion) {
                        $totalPrice += $promo->promocion->precio_promocional * $promo->cantidad; // 
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

        // Promociones activas para mostrar al usuario, cargando sus detalles y los productos asociados
        $promociones = Promocion::where('activa', 1) // 
            ->with('detalles.producto') // Carga anidada: detalles y sus productos
            ->get();


        return view('menu', compact(
            'contador',
            'productos',
            'carritoItems',
            'carrito',
            'totalPrice',
            'categorias',
            'promociones',
            'promocionItems'
        ));
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
