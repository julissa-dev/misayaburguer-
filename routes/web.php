<?php

use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\RegisterController;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::get('/productos/filtrar', [HomeController::class, 'getFilteredProducts'])->name('productos.filtrar');

Route::get('/login', [LoginController::class, 'index'])->name('login');

Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::get('/register' , [RegisterController::class, 'index'])->name('register');

Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/perfil', [PerfilController::class, 'perfil'])->name('perfil');

Route::get('/loginp', [LoginController::class, 'index'])->name('pedido');

Route::delete('/carrito/remover/{carritoItem}', [CarritoController::class, 'removeItem'])->name('carrito.remove');

Route::put('/carrito/actualizar/{carritoItem}', [CarritoController::class, 'updateQuantity'])->name('carrito.updateQuantity');

Route::get('/api/productos/buscar', [HomeController::class, 'buscarProductos'])->name('api.productos.buscar');

Route::post('/carrito/añadir', [CarritoController::class, 'añadirProducto'])->name('carrito.añadir');

Route::get('/productos/{producto:slug}', [ProductoController::class, 'show'])->name('productos.show');

Route::get('/promociones', [PromocionController::class, 'index'])->name('promocion');

Route::post('/carrito/añadir-promocion', [CarritoController::class, 'añadirPromocion'])->name('carrito.addPromo');

Route::delete('/carrito/remover-promocion/{carritoPromocion}', [CarritoController::class, 'removerPromocion'])->name('carrito.removePromo');

Route::put('/carrito/actualizar-promocion/{carritoPromocion}', [CarritoController::class, 'actualizarPromocion'])->name('carrito.updatePromo');

Route::middleware(['auth'])->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'iniciar'])->name('checkout.iniciar');
    Route::get('/checkout/{checkout}/confirmacion', [CheckoutController::class, 'confirmacion'])->name('checkout.confirmacion');
    Route::post('/checkout/{checkout}/confirmar', [CheckoutController::class, 'confirmar'])->name('checkout.confirmar');
    Route::post('/checkout/{checkout}/cancelar', [CheckoutController::class, 'cancelar'])->name('checkout.cancelar');
    Route::get('/checkout/gracias', [CheckoutController::class, 'gracias'])->name('checkout.gracias');
});






