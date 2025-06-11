<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\CarritoPromocion;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromocionController extends Controller
{
    public function index()
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

        // Promociones activas para mostrar al usuario, cargando sus detalles y los productos asociados
        $promociones = Promocion::where('activa', 1) // 
            ->with('detalles.producto') // Carga anidada: detalles y sus productos
            ->get();

        // Productos opcionales, por si se usan en la vista
        $productos = Producto::all(); // Considera cargar solo los productos necesarios si esta es una página específica de promociones

        return view('promociones', compact(
            'contador',
            'productos',
            'carritoItems',
            'carrito',
            'totalPrice',
            'promociones',
            'promocionItems'
        ));
    }
}
