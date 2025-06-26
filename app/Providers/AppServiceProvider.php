<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RepartidorMiddleware;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\CarritoPromocion;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ REGISTRO DEL MIDDLEWARE PERSONALIZADO "admin"
        Route::aliasMiddleware('admin', AdminMiddleware::class);

        Route::aliasMiddleware('repartidor', RepartidorMiddleware::class);


        // ✅ LÓGICA PARA CARRITO Y PROMOCIONES (ya estaba antes, no se borra)
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $carrito = Carrito::where('usuario_id', Auth::id())->first();

                $carritoItems = collect();
                $promocionItems = collect();
                $totalPrice = 0;
                $contador = 0;

                if ($carrito) {
                    $carritoItems = CarritoItem::with('producto')
                        ->where('carrito_id', $carrito->id)
                        ->get();

                    $promocionItems = CarritoPromocion::with(['promocion.detalles.producto'])
                        ->where('carrito_id', $carrito->id)
                        ->get();

                    $contador = $carritoItems->sum('cantidad');
                    $contador += $promocionItems->sum('cantidad');

                    foreach ($carritoItems as $item) {
                        if ($item->producto) {
                            $totalPrice += $item->cantidad * $item->producto->precio;
                        }
                    }

                    foreach ($promocionItems as $promo) {
                        if ($promo->promocion) {
                            $totalPrice += $promo->cantidad * $promo->promocion->precio_promocional;
                        }
                    }
                }

                $view->with(compact('carritoItems', 'promocionItems', 'contador', 'totalPrice'));
            } else {
                $view->with([
                    'carritoItems' => collect(),
                    'promocionItems' => collect(),
                    'contador' => 0,
                    'totalPrice' => 0
                ]);
            }
        });
    }
}
