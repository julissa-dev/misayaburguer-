<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Checkout;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Envio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PedidoConfirmadoMailable;

class CheckoutController extends Controller
{
    public function iniciar(Request $request)
    {
        $usuarioId = Auth::id();
        $carrito = Carrito::with(['items.producto', 'promociones.promocion.detalles.producto'])
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$carrito || ($carrito->items->isEmpty() && $carrito->promociones->isEmpty())) {
            return back()->with('error', 'El carrito está vacío');
        }

        $total = 0;
        foreach ($carrito->items as $item) {
            $total += $item->cantidad * $item->producto->precio;
        }
        foreach ($carrito->promociones as $promo) {
            $total += $promo->cantidad * $promo->promocion->precio_promocional;
        }

        $checkout = Checkout::create([
            'usuario_id' => $usuarioId,
            'total' => $total,
            'expira_en' => now()->addMinutes(20),
        ]);

        return redirect()->route('checkout.confirmacion', $checkout);
    }

    public function confirmacion(Checkout $checkout)
    {
        $subtotal = $checkout->total;
        $delivery = 7.0;
        $totalFinalPen = $subtotal + $delivery;

        $tipoCambio = 3.6; // Puedes actualizar esto con una API en el futuro
        $totalFinalUsd = round($totalFinalPen / $tipoCambio, 2);

        return view('checkout.confirmacion', [
            'checkout' => $checkout,
            'paypalClientId' => env('PAYPAL_CLIENT_ID'), // ✅ CORRECTO
            'totalFinalPen' => $totalFinalPen,
            'totalFinalUsd' => $totalFinalUsd
        ]);
    }

    public function confirmar(Request $request, Checkout $checkout)
    {
        if ($checkout->estado !== 'pendiente') {
            return redirect('/')->with('error', 'Este checkout ya fue procesado.');
        }



        DB::transaction(function () use ($checkout, $request) {
            $carrito = Carrito::with(['items.producto', 'promociones.promocion.detalles.producto'])
                ->where('usuario_id', $checkout->usuario_id)
                ->first();

            // ✅ Usar nueva dirección si el usuario la ingresó
            $direccion = $request->direccion_envio;
            if (empty($direccion) && !empty($request->otra_direccion)) {
                $direccion = $request->otra_direccion;
            }

            // ✅ Crear pedido
            $pedido = Pedido::create([
                'usuario_id' => $checkout->usuario_id,
                'direccion' => $direccion,
                'estado' => 'en preparacion',
                'fecha' => now(),
                'total' => 0,
            ]);




            $totalPedido = 0;

            foreach ($carrito->items as $carritoItem) {
                $producto = $carritoItem->producto;
                $precioUnitario = $producto->precio;

                $pedido->items()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $carritoItem->cantidad,
                    'precio_unit' => $precioUnitario,
                    'promocion_id' => null,
                ]);
                $totalPedido += $carritoItem->cantidad * $precioUnitario;
            }

            foreach ($carrito->promociones as $carritoPromocion) {
                $promocion = $carritoPromocion->promocion;
                $precioPromocionalTotal = $promocion->precio_promocional;
                $valorOriginalTotalPromo = 0;

                foreach ($promocion->detalles as $detalle) {
                    $valorOriginalTotalPromo += $detalle->producto->precio * $detalle->cantidad;
                }

                $factorDescuento = ($valorOriginalTotalPromo > 0)
                    ? ($precioPromocionalTotal / $valorOriginalTotalPromo) : 1;

                $itemsPromoProrrateados = [];
                $totalProrrateadoCalculado = 0;

                foreach ($promocion->detalles as $detalle) {
                    $producto = $detalle->producto;
                    $cantidadEnPromo = $detalle->cantidad;

                    $precioProrrateadoUnitario = round($producto->precio * $factorDescuento, 2);
                    $itemsPromoProrrateados[] = [
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidadEnPromo,
                        'precio_unit' => $precioProrrateadoUnitario,
                        'promocion_id' => $promocion->id,
                    ];
                    $totalProrrateadoCalculado += $cantidadEnPromo * $precioProrrateadoUnitario;
                }

                $diferenciaRedondeo = $precioPromocionalTotal - $totalProrrateadoCalculado;
                $ultimo = count($itemsPromoProrrateados) - 1;

                if ($ultimo >= 0 && abs($diferenciaRedondeo) > 0.00) {
                    $itemsPromoProrrateados[$ultimo]['precio_unit'] += $diferenciaRedondeo / $itemsPromoProrrateados[$ultimo]['cantidad'];
                    $itemsPromoProrrateados[$ultimo]['precio_unit'] = round($itemsPromoProrrateados[$ultimo]['precio_unit'], 2);
                }

                foreach ($itemsPromoProrrateados as $itemData) {
                    $pedido->items()->create($itemData);
                    $totalPedido += $itemData['cantidad'] * $itemData['precio_unit'];
                }
            }
            $delivery = 7;
            $totalPedido += $delivery; // Agregar costo de envío

            // ✅ Actualizar total del pedido
            $pedido->update(['total' => $totalPedido]);

            $pedidoConRelaciones = $pedido->load([
                'items.producto',
                'items.promocion.detalles.producto', // si deseas mostrar detalles de promociones
                'usuario',
                'pago'
            ]);
            Mail::to($pedidoConRelaciones->usuario->email)
                ->send(new PedidoConfirmadoMailable($pedidoConRelaciones));

            if ($request->hasFile('comprobante')) {
                $archivo = $request->file('comprobante');
                $ruta = $archivo->store('comprobantes', 'public'); // guarda en storage/app/public/comprobantes
                $referencia = $ruta;
            } else {
                $referencia = null;
            }

            Pago::create([
                'pedido_id' => $pedido->id,
                'metodo' => $request->metodo_pago,
                'estado' => 'pagado',
                'fecha' => now(),
                'referencia' => $referencia
            ]);

            // ✅ Crear registro de envío
            Envio::create([
                'pedido_id' => $pedido->id,
                'estado' => 'asignado',
                'actualizado_en' => now()
            ]);

            // ✅ Actualizar estado del checkout
            $checkout->update(['estado' => 'completado']);

            // ✅ Limpiar el carrito
            $carrito->items()->delete();
            $carrito->promociones()->delete();
        });

        return redirect()->route('perfil.misPedidos');
    }

    public function cancelar(Checkout $checkout)
    {
        if ($checkout->estado === 'pendiente') {
            $checkout->update(['estado' => 'cancelado']);
        }

        return redirect('/')->with('info', 'Has cancelado tu pedido.');
    }

    public function gracias()
    {
        return view('checkout.gracias');
    }
}
