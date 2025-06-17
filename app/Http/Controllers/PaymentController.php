<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Envio;
use App\Models\Checkout;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private $client;
    private $clientId;
    private $secret;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.paypal.base_uri', 'https://api-m.sandbox.paypal.com'),
        ]);

        $this->clientId = 'AVuKm9qi6S9JfyqmNZ4xmOdJ6m8SHMKPA-e03btcWPZmq8Z44G2FETkWwbnYIYXE9HQwazDqCHJiudtt';
        $this->secret = 'EGKvVfqevwf3h95pM3A_1Zu4M4VQLge__NEbg6kqGIBh-0_GD6OaOALkGIvWaJ93jno2z1iyYWtOEo5Z';
    }

    private function getAccessToken()
    {
        $response = $this->client->request('POST', '/v1/oauth2/token', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => 'grant_type=client_credentials',
            'auth' => [$this->clientId, $this->secret],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    public function confirmarPedido($orderID, Checkout $checkout)
    {
        try {
            $accessToken = $this->getAccessToken();

            // ⚠️ Captura la orden (cobro real)
            $response = $this->client->request('POST', "/v2/checkout/orders/{$orderID}/capture", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer $accessToken"
                ]
            ]);

            $orderData = json_decode($response->getBody()->getContents(), true);

            if (!isset($orderData['status']) || $orderData['status'] !== 'COMPLETED') {
                return response()->json([
                    'success' => false,
                    'message' => 'La transacción no fue completada.'
                ], 400);
            }

            DB::transaction(function () use ($checkout, $orderID) {
                $carrito = Carrito::with(['items.producto', 'promociones.promocion.detalles.producto'])
                    ->where('usuario_id', $checkout->usuario_id)
                    ->first();

                if (!$carrito) {
                    throw new \Exception('Carrito no encontrado.');
                }

                $direccion = Auth::user()->direccion ?? 'Dirección no especificada';

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
                $totalPedido += $delivery;

                $pedido->update(['total' => $totalPedido]);

                Pago::create([
                    'pedido_id' => $pedido->id,
                    'metodo' => 'paypal',
                    'estado' => 'pagado',
                    'fecha' => now(),
                    'referencia' => $orderID
                ]);

                Envio::create([
                    'pedido_id' => $pedido->id,
                    'estado' => 'asignado',
                    'actualizado_en' => now()
                ]);

                $checkout->update(['estado' => 'completado']);

                $carrito->items()->delete();
                $carrito->promociones()->delete();
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
