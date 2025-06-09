<?php

use App\Http\Controllers\CarritoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/menu', [HomeController::class, 'menu'])->name('menu');

Route::get('/login', [LoginController::class, 'index'])->name('login');

Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::get('/register' , [RegisterController::class, 'index'])->name('register');

Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/perfil', [HomeController::class, 'perfil'])->name('perfil');

Route::get('/loginp', [LoginController::class, 'index'])->name('pedido');

Route::delete('/carrito/remover/{carritoItem}', [CarritoController::class, 'removeItem'])->name('carrito.remove');

Route::put('/carrito/actualizar/{carritoItem}', [CarritoController::class, 'updateQuantity'])->name('carrito.updateQuantity');

Route::get('/api/productos/buscar', [HomeController::class, 'buscarProductos'])->name('api.productos.buscar');

Route::post('/carrito/añadir', [CarritoController::class, 'añadirProducto'])->name('carrito.añadir');

Route::get('prueba', function () {
    
    // $usuario = new Usuario();
    // $usuario->nombre = 'CRISTHIAN GIOVANY SILVA RAMIREZ';
    // $usuario->email = 'cristhian.silva@gmail.com';
    // $usuario->password = '123456';

    // $usuario->save();

    $usuario = Usuario::find(2);


    return $usuario;

})->name('prueba');



