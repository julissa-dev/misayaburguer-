<?php
namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\CarritoPromocion;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\Checkout;
use App\Models\Usuario;
class PerfilController extends Controller
{
    public function perfil()
    {
        $carrito = null;
        $carritoItems = collect();
        $promocionItems = collect();
        $contador = 0;
        $totalPrice = 0;

        if (Auth::check()) {
            $carrito = Carrito::where('usuario_id', Auth::id())->first();

            if ($carrito) {
                $carritoItems = CarritoItem::with('producto')
                    ->where('carrito_id', $carrito->id)
                    ->get();

                $contador += $carritoItems->sum('cantidad');

                foreach ($carritoItems as $item) {
                    if ($item->producto) {
                        $totalPrice += $item->producto->precio * $item->cantidad;
                    }
                }

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

        $promociones = Promocion::where('activa', 1)
            ->with('detalles.producto')
            ->get();

        $productos = Producto::all();

        return view('perfil', compact(
            'contador',
            'productos',
            'carritoItems',
            'carrito',
            'totalPrice',
            'promociones',
            'promocionItems'
        ));
    }

    public function misPedidos()
    {
        $usuarioId = Auth::id();

        $carrito = Carrito::where('usuario_id', $usuarioId)->first();
        $carritoItems = collect();
        $promocionItems = collect();
        $contador = 0;
        $totalPrice = 0;

        if ($carrito) {
            $carritoItems = CarritoItem::with('producto')->where('carrito_id', $carrito->id)->get();
            $promocionItems = CarritoPromocion::with('promocion.detalles.producto')->where('carrito_id', $carrito->id)->get();

            $contador += $carritoItems->sum('cantidad') + $promocionItems->sum('cantidad');

            foreach ($carritoItems as $item) {
                $totalPrice += $item->producto->precio * $item->cantidad;
            }
            foreach ($promocionItems as $promo) {
                $totalPrice += $promo->promocion->precio_promocional * $promo->cantidad;
            }
        }

        $pedidos = Pedido::with(['pago', 'envio', 'items.producto'])
            ->where('usuario_id', $usuarioId)
            ->orderBy('fecha', 'desc')
            ->get();

        $checkoutsPendientes = Checkout::where('usuario_id', $usuarioId)
            ->where('estado', 'pendiente')
            ->where('expira_en', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        $promociones = Promocion::where('activa', 1)->with('detalles.producto')->get();

        $productos = Producto::all();

        return view('mis_pedidos', compact(
            'contador',
            'productos',
            'carritoItems',
            'carrito',
            'totalPrice',
            'promociones',
            'promocionItems',
            'pedidos',
            'checkoutsPendientes'
        ));
    }

    // MÉTODO PARA ACTUALIZAR PERFIL
public function actualizar(Request $request)
{
    $user = Usuario::find(Auth::id());
    if ($user) {
        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
        $user->email = $request->email;
        $user->telefono = $request->telefono;
        $user->direccion = $request->direccion;
        $user->save();
    }

    return redirect()->route('perfil')->with('success', 'Perfil actualizado correctamente');
}
}
