<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer - {{ $producto->nombre }}</title> {{-- Título más descriptivo --}}
    <link rel="stylesheet" href="{{ asset('css/producto/producto.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />
    {{-- Fuente de Google Fonts para una tipografía bonita (ej: Poppins) --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    {{-- Incluye la plantilla del header --}}
    @include('partials.header', [
        'contador' => $contador ?? 0,
        'carritoItems' => $carritoItems ?? collect(),
        'totalPrice' => $totalPrice ?? 0,
    ])

    <main class="product-page-content"> {{-- Nuevo contenedor para todo el contenido principal --}}

        <section class="product-detail-main-section"> {{-- Este será el flex container para imagen e info --}}
            <div class="product-image-section">
                <img src="{{ asset('storage/img/productos/' . $producto->imagen_url) }}" alt="{{ $producto->nombre }}"
                    class="product-main-image">
            </div>

            <div class="product-info-section">
                <h1 class="product-title">{{ $producto->nombre }}</h1>
                <p class="product-category">Categoría: <a href="{{ route('menu', ['categoria_id' => $producto->categoria->id]) }}">{{ $producto->categoria->nombre }}</a>

                <div class="product-price">
                    <span>Precio:</span>
                    <span class="price-value">S/ {{ number_format($producto->precio, 2) }}</span>
                </div>

                <div class="product-description">
                    <h2>Descripción</h2>
                    <p>{{ $producto->descripcion }}</p>
                </div>

                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="quantity-btn decrease-btn" data-product-id="{{ $producto->id }}">-</button>
                        <input type="number" class="product-quantity-input" value="1" min="1"
                            max="100">
                        <button class="quantity-btn increase-btn" data-product-id="{{ $producto->id }}">+</button>
                    </div>
                    <button class="btn-agregar" data-product-id="{{ $producto->id }}">
                        <i class="fas fa-shopping-cart"></i> Añadir al Carrito
                    </button>
                </div>
            </div>
        </section>

        @if ($productosRelacionados->isNotEmpty())
            <section class="related-products"> {{-- Esta sección ahora está fuera del product-info-section --}}
                <h2>Otros productos que te podrían gustar</h2>
                <div class="related-products-grid">
                    @foreach ($productosRelacionados as $relacionado)
                        <div class="related-product-card">
                            <img src="{{ asset('storage/img/productos/' . $relacionado->imagen_url) }}"
                                alt="{{ $relacionado->nombre }}">
                            <h3>{{ $relacionado->nombre }}</h3>
                            <p>S/ {{ number_format($relacionado->precio, 2) }}</p>
                            <a href="{{ route('productos.show', $relacionado->slug ?? $relacionado->id) }}"
                                class="btn-ver-producto">Ver Producto</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </main>

    <script>
        window.routes = {
            productosFiltrar: "{{ route('productos.filtrar') }}",
            perfil: "{{ route('perfil') }}",
            login: "{{ route('login') }}",
            addToCart: "{{ route('carrito.añadir') }}"
        };

        // Función para añadir al carrito desde la vista de detalle del producto
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartBtn = document.querySelector('.btn-agregar');
            const quantityInput = document.querySelector('.product-quantity-input');

            if (addToCartBtn && quantityInput) {
                addToCartBtn.addEventListener('click', async function() {
                    const productId = this.dataset.productId;
                    const quantity = parseInt(quantityInput.value);

                    try {
                        const response = await fetch(window.routes.addToCart, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                producto_id: productId,
                                cantidad: quantity
                            })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message ||
                                'Error al añadir el producto al carrito.');
                        }

                        const data = await response.json();
                        if (data.success) {
                            window.updateCartUI(data); // Asume que updateCartUI es global de header.js
                            Swal.fire({
                                icon: 'success',
                                title: '¡Añadido!',
                                text: 'Producto añadido al carrito con éxito.',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo añadir el producto.',
                                'error');
                        }
                    } catch (error) {
                        console.error('Error al añadir al carrito:', error);
                        Swal.fire('Error', 'Hubo un problema al añadir el producto: ' + error.message,
                            'error');
                    }
                });
            }

            // Lógica para los botones de cantidad
            const quantityDecreaseBtn = document.querySelector('.quantity-btn.decrease-btn');
            const quantityIncreaseBtn = document.querySelector('.quantity-btn.increase-btn');

            if (quantityDecreaseBtn && quantityInput && quantityIncreaseBtn) {
                quantityDecreaseBtn.addEventListener('click', () => {
                    let currentValue = parseInt(quantityInput.value);
                    if (currentValue > 1) {
                        quantityInput.value = currentValue - 1;
                    }
                });

                quantityIncreaseBtn.addEventListener('click', () => {
                    let currentValue = parseInt(quantityInput.value);
                    quantityInput.value = currentValue + 1;
                });
            }
        });
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
</body>

</html>
