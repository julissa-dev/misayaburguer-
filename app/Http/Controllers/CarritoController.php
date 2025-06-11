<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\CarritoPromocion;
use App\Models\Producto; // Asegúrate de importar el modelo Producto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Promocion;

class CarritoController extends Controller
{
    // ... (Tu método index actual) ...

    /**
     * Elimina un producto del carrito.
     * @param CarritoItem $carritoItem La instancia del CarritoItem a eliminar.
     */

    public function añadirProducto(Request $request)
    {
        // 1. Validar la solicitud
        $request->validate([
            'producto_id' => 'required|exists:productos,id', // Asegura que el ID del producto es requerido y existe en la tabla 'productos'
            'cantidad' => 'nullable|integer|min:1',       // Cantidad es opcional, debe ser un entero y al menos 1
        ]);

        $productoId = $request->input('producto_id');
        $cantidad = $request->input('cantidad', 1); // Si no se envía cantidad, por defecto es 1

        // 2. Asegurarse de que el usuario esté autenticado
        if (!Auth::check()) {
            // Si el usuario no está autenticado, puedes redirigirlo al login
            // o, para una API AJAX, devolver un error 401 (Unauthorized)
            return response()->json(['success' => false, 'message' => 'Debes iniciar sesión para añadir productos al carrito.'], 401);
        }

        $userId = Auth::id();

        try {
            // 3. Obtener el carrito del usuario o crear uno si no existe
            // firstOrCreate intentará encontrar un carrito para el usuario.
            // Si no lo encuentra, creará uno con los atributos proporcionados.
            $carrito = Carrito::firstOrCreate(
                ['usuario_id' => $userId],
                ['estado' => 'activo'] // Puedes agregar otros atributos por defecto para un nuevo carrito
            );

            // 4. Buscar el ítem del producto en el carrito del usuario
            $carritoItem = CarritoItem::where('carrito_id', $carrito->id)
                ->where('producto_id', $productoId)
                ->first();

            // 5. Si el ítem ya existe, actualizar la cantidad; de lo contrario, crearlo
            if ($carritoItem) {
                // El producto ya está en el carrito, solo actualiza la cantidad
                $carritoItem->cantidad += $cantidad;
                $carritoItem->save();
                $message = 'Cantidad del producto actualizada en el carrito.';
            } else {
                // El producto no está en el carrito, creamos un nuevo CarritoItem
                // Primero, obtenemos el producto para asegurarnos de que el precio unitario sea correcto.
                $producto = Producto::find($productoId);
                if (!$producto) {
                    // Esto debería ser capturado por la validación 'exists', pero es una doble seguridad.
                    return response()->json(['success' => false, 'message' => 'El producto especificado no fue encontrado.'], 404);
                }

                CarritoItem::create([
                    'carrito_id' => $carrito->id,
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio, // Almacenar el precio en el momento de la adición
                ]);
                $message = 'Producto añadido al carrito.';
            }

            // 6. Recargar los ítems del carrito y calcular el nuevo total/cantidad para la UI
            // Reutilizamos tus métodos de ayuda existentes
            $updatedCarritoItems = $this->getCartItemsForUser($userId);
            $newTotalPrice = $this->calculateCartTotal($updatedCarritoItems);
            $newTotalQuantity = $updatedCarritoItems->sum('cantidad'); // Suma de la columna 'cantidad' de todos los items

            // 7. Renderizar los ítems del carrito como HTML para enviar al frontend
            // Asumo que tienes una vista parcial en 'resources/views/partials/cart_items.blade.php'
            $itemsHtml = view('partials.cart_items', ['carritoItems' => $updatedCarritoItems])->render();

            // 8. Devolver la respuesta JSON
            return response()->json([
                'success' => true,
                'message' => $message,
                'itemsHtml' => $itemsHtml,
                'totalPrice' => $newTotalPrice,
                'totalQuantity' => $newTotalQuantity
            ]);
        } catch (\Exception $e) {
            // Capturar cualquier excepción inesperada
            return response()->json(['success' => false, 'message' => 'Error al procesar la solicitud: ' . $e->getMessage()], 500);
        }
    }

    

    public function removeItem(CarritoItem $carritoItem)
    {

        $carritoItem->load('carrito');
        // Asegúrate de que el CarritoItem pertenezca al usuario autenticado para seguridad
        if (Auth::id() != $carritoItem->carrito->usuario_id) {
            return response()->json(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }

        try {
            $carritoItem->delete(); // Elimina el ítem del carrito

            // Después de eliminar, recarga el carrito para obtener los nuevos datos
            $updatedCarritoItems = $this->getCartItemsForUser(Auth::id());
            $newTotalPrice = $this->calculateCartTotal($updatedCarritoItems);
            $newTotalQuantity = $updatedCarritoItems->sum('cantidad');

            // Renderiza los ítems del carrito de nuevo (o pasa los datos para que JS los renderice)
            // Una forma común es pasar el HTML ya renderizado desde el servidor
            $itemsHtml = view('partials.cart_items', ['carritoItems' => $updatedCarritoItems])->render();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito.',
                'itemsHtml' => $itemsHtml,
                'totalPrice' => $newTotalPrice,
                'totalQuantity' => $newTotalQuantity
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el producto: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     * @param Request $request
     * @param CarritoItem $carritoItem La instancia del CarritoItem a actualizar.
     */
    public function updateQuantity(Request $request, CarritoItem $carritoItem)
    {
        $carritoItem->load('carrito');
        // Asegúrate de que el CarritoItem pertenezca al usuario autenticado para seguridad
        if (Auth::id() != $carritoItem->carrito->usuario_id) {
            return response()->json(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }

        $action = $request->input('action'); // 'increase' o 'decrease'

        try {
            if ($action === 'increase') {
                $carritoItem->cantidad++;
            } elseif ($action === 'decrease') {
                $carritoItem->cantidad--;
            } else {
                return response()->json(['success' => false, 'message' => 'Acción no válida.'], 400);
            }

            // Si la cantidad llega a cero o menos, se elimina el ítem
            if ($carritoItem->cantidad <= 0) {
                $carritoItem->delete();
            } else {
                $carritoItem->save();
            }

            // Después de actualizar, recarga el carrito para obtener los nuevos datos
            $updatedCarritoItems = $this->getCartItemsForUser(Auth::id());
            $newTotalPrice = $this->calculateCartTotal($updatedCarritoItems);
            $newTotalQuantity = $updatedCarritoItems->sum('cantidad');

            $itemsHtml = view('partials.cart_items', ['carritoItems' => $updatedCarritoItems])->render();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada.',
                'itemsHtml' => $itemsHtml,
                'totalPrice' => $newTotalPrice,
                'totalQuantity' => $newTotalQuantity
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la cantidad: ' . $e->getMessage()], 500);
        }
    }

    // --- Métodos de Ayuda (pueden ser privados o helpers) ---

    /**
     * Obtiene los ítems del carrito para un usuario específico, con sus productos relacionados.
     * @param int $userId El ID del usuario.
     * @return \Illuminate\Support\Collection
     */
    private function getCartItemsForUser(int $userId)
    {
        $carrito = Carrito::where('usuario_id', $userId)->first();
        if ($carrito) {
            return CarritoItem::with('producto')
                ->where('carrito_id', $carrito->id)
                ->get();
        }
        return collect(); // Devuelve una colección vacía si no hay carrito
    }

    /**
     * Calcula el precio total de los ítems del carrito.
     * @param \Illuminate\Support\Collection $carritoItems
     * @return float
     */
    private function calculateCartTotal(\Illuminate\Support\Collection $carritoItems)
    {
        $total = 0;
        foreach ($carritoItems as $item) {
            if ($item->producto) { // Asegúrate de que el producto exista
                $total += $item->producto->precio * $item->cantidad;
            }
        }
        return $total;
    }

}
