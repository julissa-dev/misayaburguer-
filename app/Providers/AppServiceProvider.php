<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Carrito;
use App\Models\CarritoItem; // Asegúrate de importar CarritoItem
use App\Models\CarritoPromocion; // Asegúrate de importar CarritoPromocion

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
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $carrito = Carrito::where('usuario_id', Auth::id())->first();

                $carritoItems = collect();
                $promocionItems = collect();
                $totalPrice = 0;
                $contador = 0; // Inicializar aquí

                if ($carrito) {
                    $carritoItems = CarritoItem::with('producto')
                        ->where('carrito_id', $carrito->id)
                        ->get();

                    $promocionItems = CarritoPromocion::with(['promocion.detalles.producto'])
                        ->where('carrito_id', $carrito->id)
                        ->get();

                    // --- CAMBIO CLAVE AQUÍ ---
                    // Calcular el contador sumando las cantidades de ambos tipos de ítems
                    $contador = $carritoItems->sum('cantidad');
                    $contador += $promocionItems->sum('cantidad'); // Suma las cantidades de las promociones (cuántas promos completas)

                    // Recalcular el totalPrice de forma consistente aquí también
                    foreach ($carritoItems as $item) {
                        if ($item->producto) { // Siempre es buena práctica verificar
                            $totalPrice += $item->cantidad * $item->producto->precio;
                        }
                    }

                    foreach ($promocionItems as $promo) {
                        if ($promo->promocion) { // Siempre es buena práctica verificar
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