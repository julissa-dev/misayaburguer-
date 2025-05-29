<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto; // Asegúrate de importar el modelo Producto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    // ... (Tu método index actual) ...

    /**
     * Elimina un producto del carrito.
     * @param CarritoItem $carritoItem La instancia del CarritoItem a eliminar.
     */
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