<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer</title>
    <link rel="stylesheet" href="css/home.css" />
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    {{-- Incluye la plantilla del header --}}
    @include('partials.header', [
        'contador' => $contador ?? 0, // Asegúrate de pasar estas variables si no están disponibles globalmente
        'carritoItems' => $carritoItems ?? collect(),
        'totalPrice' => $totalPrice ?? 0,
    ])



    {{-- Tu JavaScript puede quedarse en el archivo principal si es específico de la página,
         o moverlo a un archivo .js externo si es global. --}}
    <script>
        const toggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav-links');

        toggle.addEventListener('click', () => {
            nav.classList.toggle('active');
        });

        const userDropdownToggle = document.getElementById('userDropdownToggle');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdownToggle && userDropdownMenu) {
            userDropdownToggle.addEventListener('click', function(event) {
                event.preventDefault();
                userDropdownMenu.classList.toggle('show');
            });

            window.addEventListener('click', function(event) {
                if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    if (userDropdownMenu.classList.contains('show')) {
                        userDropdownMenu.classList.remove('show');
                    }
                }
            });
        }

        const logoutForm = document.getElementById('logout-form');

        if (logoutForm) {
            logoutForm.addEventListener('submit', function(event) {
                event.preventDefault();

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Estás a punto de cerrar tu sesión.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cerrar sesión',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        }

        const cartButton = document.getElementById('cartButton');
        const cartDropdown = document.getElementById('cartDropdown');
        const closeCartBtn = document.getElementById('closeCartBtn');
        const cartDropdownItems = document.getElementById('cartDropdownItems');
        const cartTotalPriceElement = document.getElementById('cartTotalPrice');
        const cartCountElement = document.querySelector('.cart-count');

        if (cartButton && cartDropdown) {
            cartButton.addEventListener('click', function(event) {
                event.stopPropagation();
                cartDropdown.classList.toggle('show-cart');
            });

            closeCartBtn.addEventListener('click', function() {
                cartDropdown.classList.remove('show-cart');
            });

            window.addEventListener('click', function(event) {
                if (!cartButton.contains(event.target) && !cartDropdown.contains(event.target)) {
                    if (cartDropdown.classList.contains('show-cart')) {
                        cartDropdown.classList.remove('show-cart');
                    }
                }
            });

            function updateCartUI(response) {
                if (response && response.itemsHtml !== undefined && response.totalPrice !== undefined && response
                    .totalQuantity !== undefined) {
                    cartDropdownItems.innerHTML = response.itemsHtml;
                    cartTotalPriceElement.textContent = `$${response.totalPrice.toFixed(2)}`;
                    cartCountElement.textContent = response.totalQuantity;
                } else {
                    console.error('Respuesta AJAX inválida:', response);
                    Swal.fire('Error', 'No se pudo actualizar el carrito. Datos incompletos.', 'error');
                }
            }

            cartDropdownItems.addEventListener('click', async function(event) {
                const target = event.target;

                if (target.classList.contains('remove-item-btn')) {
                    const itemId = target.dataset.itemId;
                    if (!itemId) return;

                    Swal.fire({
                        title: '¿Eliminar producto?',
                        text: "¿Estás seguro de que quieres quitar este producto del carrito?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const response = await fetch(`/carrito/remover/${itemId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').getAttribute(
                                            'content'),
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                });

                                if (!response.ok) {
                                    const errorData = await response.json();
                                    throw new Error(errorData.message ||
                                        'Error al eliminar el producto');
                                }

                                const data = await response.json();
                                if (data.success) {
                                    updateCartUI(data);
                                    Swal.fire('Eliminado!',
                                        'El producto ha sido eliminado del carrito.', 'success');
                                } else {
                                    Swal.fire('Error', data.message ||
                                        'No se pudo eliminar el producto.', 'error');
                                }
                            } catch (error) {
                                console.error('Error al eliminar del carrito:', error);
                                Swal.fire('Error', 'Hubo un problema al eliminar el producto: ' +
                                    error.message, 'error');
                            }
                        }
                    });
                }

                if (target.classList.contains('quantity-btn')) {
                    const itemId = target.dataset.itemId;
                    const action = target.dataset.action;
                    if (!itemId || !action) return;

                    try {
                        const response = await fetch(`/carrito/actualizar/${itemId}`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                action: action
                            })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || 'Error al actualizar la cantidad');
                        }

                        const data = await response.json();
                        if (data.success) {
                            updateCartUI(data);
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo actualizar la cantidad.', 'error');
                        }
                    } catch (error) {
                        console.error('Error al actualizar cantidad:', error);
                        Swal.fire('Error', 'Hubo un problema al actualizar la cantidad: ' + error.message,
                            'error');
                    }
                }
            });

            document.querySelectorAll('.btn-add-to-cart').forEach(button => {
                button.addEventListener('click', async function() {
                    const productId = this.dataset.productId;
                    try {
                        const response = await fetch('/carrito/añadir', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                producto_id: productId,
                                cantidad: 1
                            })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message ||
                                'Error al añadir el producto al carrito');
                        }

                        const data = await response.json();
                        if (data.success) {
                            updateCartUI(data);
                            Swal.fire('Añadido!', 'Producto añadido al carrito.', 'success');
                        } else {
                            Swal.fire('Error', data.message ||
                                'No se pudo añadir el producto al carrito.', 'error');
                        }
                    } catch (error) {
                        console.error('Error al añadir al carrito:', error);
                        Swal.fire('Error', 'Hubo un problema al añadir el producto: ' + error.message,
                            'error');
                    }
                });
            });
        }

        const searchInput = document.getElementById('searchInput');
        const searchResultsDiv = document.getElementById('searchResults');
        let searchTimeout;

        if (searchInput && searchResultsDiv) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout); // Limpiar el timeout anterior
                const query = this.value.trim();

                if (query.length > 0) {
                    // Pequeño retardo para no hacer una petición por cada letra
                    searchTimeout = setTimeout(async () => {
                        try {
                            const response = await fetch(
                                `/api/productos/buscar?query=${encodeURIComponent(query)}`, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });

                            if (!response.ok) {
                                throw new Error('Error al buscar productos.');
                            }

                            const products = await response.json();
                            displaySearchResults(products);
                        } catch (error) {
                            console.error('Error en la búsqueda:', error);
                            searchResultsDiv.innerHTML =
                                '<p class="no-results">Error al cargar resultados.</p>';
                            searchResultsDiv.classList.add('show');
                        }
                    }, 300); // Espera 300ms después de que el usuario deja de escribir
                } else {
                    searchResultsDiv.innerHTML = '';
                    searchResultsDiv.classList.remove('show');
                }
            });

            // Ocultar resultados si se hace clic fuera del input y del dropdown
            document.addEventListener('click', function(event) {
                if (!searchInput.contains(event.target) && !searchResultsDiv.contains(event.target)) {
                    searchResultsDiv.classList.remove('show');
                }
            });

            function displaySearchResults(products) {
                searchResultsDiv.innerHTML = ''; // Limpiar resultados anteriores

                if (products.length === 0) {
                    searchResultsDiv.innerHTML = '<p class="no-results">No se encontraron productos.</p>';
                } else {
                    products.forEach(product => {
                        const productHtml = `
                        <div class="search-result-item">
                            <img src="${product.imagen_url || 'img/placeholder.png'}" alt="${product.nombre}">
                            <div class="details">
                                <h4>${product.nombre}</h4>
                                <p>$${parseFloat(product.precio).toFixed(2)}</p>
                            </div>
                            <div class="actions">
                                <button class="btn-add-to-cart-search" data-product-id="${product.id}">Añadir</button>
                                <a href="/productos/${product.id}" class="btn-view-details">Ver</a>
                            </div>
                        </div>
                    `;
                        searchResultsDiv.insertAdjacentHTML('beforeend', productHtml);
                    });
                }
                searchResultsDiv.classList.add('show'); // Mostrar el dropdown
            }

            // Delegación de eventos para los botones de añadir al carrito en los resultados de búsqueda
            searchResultsDiv.addEventListener('click', async function(event) {
                const target = event.target;

                if (target.classList.contains('btn-add-to-cart-search')) {
                    const productId = target.dataset.productId;
                    if (!productId) return;

                    try {
                        const response = await fetch('/carrito/añadir', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                producto_id: productId,
                                cantidad: 1
                            })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || 'Error al añadir el producto al carrito');
                        }

                        const data = await response.json();
                        if (data.success) {
                            updateCartUI(data); // Reutiliza la función existente para actualizar el carrito
                            Swal.fire('Añadido!', 'Producto añadido al carrito.', 'success');
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo añadir el producto al carrito.',
                                'error');
                        }
                    } catch (error) {
                        console.error('Error al añadir al carrito desde la búsqueda:', error);
                        Swal.fire('Error', 'Hubo un problema al añadir el producto: ' + error.message, 'error');
                    }
                }
                // Los enlaces "Ver" ya funcionan por sí solos porque son <a> tags.
                // Asegúrate de tener una ruta `/productos/{id}` en tu `web.php`
                // y un controlador para mostrar los detalles del producto.
                // Por ejemplo: Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
            });
        }
    </script>
</body>

</html>
