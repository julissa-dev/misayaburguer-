<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Categoria;
use App\Models\Pedido;
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

    public function processOrder(Request $request)
    {
        // ... (Lógica para obtener el carrito del usuario, validar, etc.)

        $carrito = Carrito::where('usuario_id', Auth::id())->first(); // O como obtengas el carrito actual

        // Crear el nuevo Pedido
        $pedido = Pedido::create([
            'usuario_id' => $carrito->usuario_id,
            'direccion' => $request->direccion_envio,
            'estado' => 'pendiente',
            'fecha' => now(),
            'total' => 0, // Se actualizará al final
        ]);

        $totalPedido = 0; // Para calcular el total del pedido

        // --- Procesar ítems individuales del carrito (si los hay) ---
        foreach ($carrito->items as $carritoItem) {
            // Asume que carrito_items es para productos individuales, no promociones
            $producto = $carritoItem->producto; // Obtener el modelo del producto
            $precioUnitario = $producto->precio; // Precio normal del producto

            $pedido->items()->create([
                'producto_id' => $producto->id,
                'cantidad' => $carritoItem->cantidad,
                'precio_unit' => $precioUnitario,
                'promocion_id' => null, // No es parte de una promoción
            ]);
            $totalPedido += $carritoItem->cantidad * $precioUnitario;
        }

        // --- Procesar promociones del carrito ---
        foreach ($carrito->promociones as $carritoPromocion) {
            $promocion = $carritoPromocion->promocion; // Obtener el modelo de la promoción
            $precioPromocionalTotal = $promocion->precio_promocional;

            $valorOriginalTotalPromo = 0;
            foreach ($promocion->detalle as $detalle) {
                $valorOriginalTotalPromo += $detalle->producto->precio * $detalle->cantidad;
            }

            // Evitar división por cero
            $factorDescuento = ($valorOriginalTotalPromo > 0) ? ($precioPromocionalTotal / $valorOriginalTotalPromo) : 1;

            // Iterar sobre los productos que componen la promoción para crear los pedido_items
            $itemsPromoProrrateados = [];
            $totalProrrateadoCalculado = 0;

            foreach ($promocion->detalle as $detalle) {
                $producto = $detalle->producto;
                $cantidadEnPromo = $detalle->cantidad; // Cantidad de este producto en la promoción

                // Calcular el precio prorrateado para cada unidad de este producto en la promoción
                $precioProrrateadoUnitario = round($producto->precio * $factorDescuento, 2); // Redondeo a 2 decimales

                $itemsPromoProrrateados[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidadEnPromo,
                    'precio_unit' => $precioProrrateadoUnitario,
                    'promocion_id' => $promocion->id, // ¡Aquí se asigna el ID de la promoción!
                ];
                $totalProrrateadoCalculado += $cantidadEnPromo * $precioProrrateadoUnitario;
            }

            // Manejar el redondeo final: Si hay una pequeña diferencia por el redondeo, ajústala
            $diferenciaRedondeo = $precioPromocionalTotal - $totalProrrateadoCalculado;

            if (abs($diferenciaRedondeo) > 0.00) { // Si hay una diferencia (ej. 0.01 o -0.01)
                // Ajustar el último ítem (o el más caro) para absorber la diferencia
                $ultimoItemIndex = count($itemsPromoProrrateados) - 1;
                if ($ultimoItemIndex >= 0) {
                    $itemsPromoProrrateados[$ultimoItemIndex]['precio_unit'] += $diferenciaRedondeo / $itemsPromoProrrateados[$ultimoItemIndex]['cantidad'];
                    // O simplemente al primer item o al más caro para evitar divisiones
                    $itemsPromoProrrateados[$ultimoItemIndex]['precio_unit'] = round($itemsPromoProrrateados[$ultimoItemIndex]['precio_unit'], 2);
                }
            }


            // Crear los pedido_items para la promoción
            foreach ($itemsPromoProrrateados as $itemData) {
                $pedido->items()->create($itemData);
                $totalPedido += $itemData['cantidad'] * $itemData['precio_unit'];
            }
        }

        // Actualizar el total final del pedido
        $pedido->update(['total' => $totalPedido]);

        // ... (Lógica para procesar el pago, crear envío, etc.)
    }
}
