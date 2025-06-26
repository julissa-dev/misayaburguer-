<?php

use App\Http\Controllers\Admin\AdminCategoriaController;
use App\Http\Controllers\Admin\AdminProductoController;
use App\Http\Controllers\Admin\AdminPromocionController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\RegisterController;
use App\Mail\PedidoConfirmadoMailable;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Resend\Laravel\Facades\Resend;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Repartidor\DeliveryController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::get('/productos/filtrar', [HomeController::class, 'getFilteredProducts'])->name('productos.filtrar');

Route::get('/login', [LoginController::class, 'index'])->name('login');

Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::get('/register', [RegisterController::class, 'index'])->name('register');

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
    Route::get('/paypal/confirmar-pedido/{orderID}/{checkout}', [PaymentController::class, 'confirmarPedido']);

    Route::get('/mis-pedidos', [PerfilController::class, 'misPedidos'])->name('perfil.misPedidos');
});

Route::get('/api/pedidos/{pedido}', [PedidoController::class, 'mostrar'])->middleware('auth');


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('usuarios', UserController::class)->names([
        'index' => 'admin.usuarios.index',
        'create' => 'admin.usuarios.create',
        'store' => 'admin.usuarios.store',
        'show' => 'admin.usuarios.show', // Puedes eliminarla si no necesitas ver un usuario individual
        'edit' => 'admin.usuarios.edit',
        'update' => 'admin.usuarios.update',
        'destroy' => 'admin.usuarios.destroy',
    ]);

    // Gestión de Productos
    // Gestión de Productos (¡Usando el controlador nombrado AdminProductoController!)
    Route::resource('productos', AdminProductoController::class)
        ->parameters(['productos' => 'producto:slug']) // <-- Esta línea es clave
        ->names([
            'index' => 'admin.productos.index',
            'create' => 'admin.productos.create',
            'store' => 'admin.productos.store',
            'show' => 'admin.productos.show',
            'edit' => 'admin.productos.edit',
            'update' => 'admin.productos.update',
            'destroy' => 'admin.productos.destroy',
        ]);

    // Gestión de Promociones (¡Usando el controlador nombrado AdminPromocionController!)
    Route::get('promociones', [AdminPromocionController::class, 'index'])->name('admin.promociones.index');

    // GET /admin/promociones/create (Mostrar formulario de creación)
    Route::get('promociones/create', [AdminPromocionController::class, 'create'])->name('admin.promociones.create');

    // POST /admin/promociones (Guardar nueva promoción)
    Route::post('promociones', [AdminPromocionController::class, 'store'])->name('admin.promociones.store');

    // GET /admin/promociones/{promocion}/add-products (Mostrar formulario para añadir productos a una promoción existente)
    Route::get('promociones/{promocion}/add-products', [AdminPromocionController::class, 'addProductForm'])->name('admin.promociones.add-products');

    // POST /admin/promociones/{promocion}/add-products (Guardar productos en una promoción existente)
    Route::post('promociones/{promocion}/add-products', [AdminPromocionController::class, 'saveProducts'])->name('admin.promociones.save-products');

    // GET /admin/promociones/{promocion} (Mostrar detalles de una promoción específica)
    // El {promocion} aquí asume que Laravel hará Model Binding automáticamente
    Route::get('promociones/{promocion}', [AdminPromocionController::class, 'show'])->name('promociones.show');

    // GET /admin/promociones/{promocion}/edit (Mostrar formulario de edición)
    Route::get('promociones/{promocion}/edit', [AdminPromocionController::class, 'edit'])->name('admin.promociones.edit');

    // PUT/PATCH /admin/promociones/{promocion} (Actualizar una promoción existente)
    Route::put('promociones/{promocion}', [AdminPromocionController::class, 'update'])->name('admin.promociones.update');
    // También es buena práctica añadir el PATCH si tu formulario lo usa, aunque PUT es más común para resource
    // Route::patch('promociones/{promocion}', [AdminPromocionController::class, 'update'])->name('promociones.update');


    // DELETE /admin/promociones/{promocion} (Eliminar una promoción)
    Route::delete('promociones/{promocion}', [AdminPromocionController::class, 'destroy'])->name('admin.promociones.destroy');

    Route::resource('categorias', AdminCategoriaController::class)->names([
        'index' => 'admin.categorias.index',
        'create' => 'admin.categorias.create',
        'store' => 'admin.categorias.store',
        'show' => 'admin.categorias.show', // Podrías no necesitar 'show' para categorías
        'edit' => 'admin.categorias.edit',
        'update' => 'admin.categorias.update',
        'destroy' => 'admin.categorias.destroy',
    ]);

    Route::get('/reportes/generar', [ReporteController::class, 'generarVentas'])->name('admin.reportes.generar');

    Route::resource('inventario', InsumoController::class)
        ->parameters(['inventario' => 'insumo'])
        ->names('inventario');

    Route::get('/pedidos', [PedidoController::class, 'adminPedidos'])->name('admin.pedidos.index');

    Route::get('/pedidos/{pedido}/asignar', [PedidoController::class, 'showAssignRepartidorForm'])->name('admin.pedidos.assign_form');
    Route::post('/pedidos/{pedido}/asignar', [PedidoController::class, 'assignRepartidor'])->name('admin.pedidos.assign');

    Route::get('/pedidos/{pedido}/detalles', [PedidoController::class, 'detalles'])->name('admin.pedidos.detalles');

    Route::post('/pedidos/confirmar', [PedidoController::class, 'confirmarVenta'])->name('admin.pedidos.confirmar');



    Route::get('/pedidos/{pedido}/movimientos', [PedidoController::class, 'movimientos'])->name('admin.pedidos.movimientos');
    Route::resource('proveedores', ProveedorController::class)->names('admin.proveedores');
});

Route::prefix('repartidor')
    ->name('repartidor.')
    ->middleware(['auth', 'repartidor']) // <--- usamos el alias aquí
    ->group(function () {
        Route::get('/', [DeliveryController::class, 'index'])->name('dashboard');
        Route::get('pedidos/{envio}', [DeliveryController::class, 'show'])->name('pedidos.show');
        Route::get('historial', [DeliveryController::class, 'history'])->name('historial');
        Route::put('envios/{envio}/actualizar-estado', [DeliveryController::class, 'updateStatus'])->name('envios.update_status');
    });

Route::get('/prueba', [RegisterController::class, 'prueba']);
Route::put('/perfil/actualizar', [PerfilController::class, 'actualizar'])->name('perfil.actualizar');