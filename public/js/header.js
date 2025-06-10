// header.js

document.addEventListener('DOMContentLoaded', () => {
    // Menu Toggle
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav-links');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('active');
        });
    }

    // User Dropdown
    const userDropdownToggle = document.getElementById('userDropdownToggle');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    if (userDropdownToggle && userDropdownMenu) {
        userDropdownToggle.addEventListener('click', function(event) {
            event.preventDefault();
            userDropdownMenu.classList.toggle('show');
            // Close other dropdowns if open
            const deliveryDropdownMenu = document.getElementById('deliveryDropdownMenu');
            const cartDropdown = document.getElementById('cartDropdown');
            if (deliveryDropdownMenu) deliveryDropdownMenu.classList.remove('show');
            if (cartDropdown) cartDropdown.classList.remove('show-cart');
        });

        window.addEventListener('click', function(event) {
            if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                if (userDropdownMenu.classList.contains('show')) {
                    userDropdownMenu.classList.remove('show');
                }
            }
        });
    }

    // Logout Form with SweetAlert
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

    // Cart Dropdown
    const cartButton = document.getElementById('cartButton');
    const cartDropdown = document.getElementById('cartDropdown');
    const closeCartBtn = document.getElementById('closeCartBtn');
    const cartDropdownItems = document.getElementById('cartDropdownItems');
    const cartTotalPriceElement = document.getElementById('cartTotalPrice');
    const cartCountElement = document.querySelector('.cart-count');

    // Make updateCartUI global or pass it around if needed by other modules,
    // or ensure cart updates are handled by a dedicated cart module.
    // For now, let's keep it here, but ideally, this would be in a cart.js
    // if cart functionality is extensive.
    window.updateCartUI = function(response) {
        if (response && response.itemsHtml !== undefined && response.totalPrice !== undefined && response.totalQuantity !== undefined) {
            cartDropdownItems.innerHTML = response.itemsHtml;
            cartTotalPriceElement.textContent = `$${response.totalPrice.toFixed(2)}`;
            cartCountElement.textContent = response.totalQuantity;
        } else {
            console.error('Respuesta AJAX inválida:', response);
            Swal.fire('Error', 'No se pudo actualizar el carrito. Datos incompletos.', 'error');
        }
    };

    if (cartButton && cartDropdown) {
        cartButton.addEventListener('click', function(event) {
            event.stopPropagation();
            cartDropdown.classList.toggle('show-cart');
            // Close other dropdowns if open
            if (userDropdownMenu) userDropdownMenu.classList.remove('show');
            const deliveryDropdownMenu = document.getElementById('deliveryDropdownMenu');
            if (deliveryDropdownMenu) deliveryDropdownMenu.classList.remove('show');
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

        // Event delegation for cart item removal and quantity updates
        if (cartDropdownItems) { // Check if element exists before adding listener
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
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                });

                                if (!response.ok) {
                                    const errorData = await response.json();
                                    throw new Error(errorData.message || 'Error al eliminar el producto');
                                }

                                const data = await response.json();
                                if (data.success) {
                                    window.updateCartUI(data); // Use the global updateCartUI
                                    Swal.fire('Eliminado!', 'El producto ha sido eliminado del carrito.', 'success');
                                } else {
                                    Swal.fire('Error', data.message || 'No se pudo eliminar el producto.', 'error');
                                }
                            } catch (error) {
                                console.error('Error al eliminar del carrito:', error);
                                Swal.fire('Error', 'Hubo un problema al eliminar el producto: ' + error.message, 'error');
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
                            window.updateCartUI(data); // Use the global updateCartUI
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo actualizar la cantidad.', 'error');
                        }
                    } catch (error) {
                        console.error('Error al actualizar cantidad:', error);
                        Swal.fire('Error', 'Hubo un problema al actualizar la cantidad: ' + error.message, 'error');
                    }
                }
            });
        }
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchResultsDiv = document.getElementById('searchResults');
    let searchTimeout;

    if (searchInput && searchResultsDiv) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length > 0) {
                searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`/api/productos/buscar?query=${encodeURIComponent(query)}`, {
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
                        searchResultsDiv.innerHTML = '<p class="no-results">Error al cargar resultados.</p>';
                        searchResultsDiv.classList.add('show');
                    }
                }, 300);
            } else {
                searchResultsDiv.innerHTML = '';
                searchResultsDiv.classList.remove('show');
            }
        });

        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchResultsDiv.contains(event.target)) {
                searchResultsDiv.classList.remove('show');
            }
        });

        function displaySearchResults(products) {
            searchResultsDiv.innerHTML = '';

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
            searchResultsDiv.classList.add('show');
        }

        // Event delegation for "Add to Cart" buttons in search results
        searchResultsDiv.addEventListener('click', async function(event) {
            const target = event.target;

            if (target.classList.contains('btn-add-to-cart-search')) {
                const productId = target.dataset.productId;
                if (!productId) return;

                try {
                    const response = await fetch('/carrito/añadir', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
                        window.updateCartUI(data);
                        Swal.fire('Añadido!', 'Producto añadido al carrito.', 'success');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo añadir el producto al carrito.', 'error');
                    }
                } catch (error) {
                    console.error('Error al añadir al carrito desde la búsqueda:', error);
                    Swal.fire('Error', 'Hubo un problema al añadir el producto: ' + error.message, 'error');
                }
            }
        });
    }

    // Delivery Dropdown
    const deliveryToggle = document.getElementById('deliveryToggle');
    const deliveryDropdownMenu = document.getElementById('deliveryDropdownMenu');
    const closeDeliveryBtn = document.getElementById('closeDeliveryBtn');
    const manageAddressBtn = document.getElementById('manageAddressBtn');
    const btnLoginBtn = document.getElementById('btnLoginBtn');

    if (deliveryToggle && deliveryDropdownMenu && closeDeliveryBtn) {
        deliveryToggle.addEventListener('click', function(event) {
            event.preventDefault();
            deliveryDropdownMenu.classList.toggle('show');
            // Close other dropdowns if open
            if (userDropdownMenu) userDropdownMenu.classList.remove('show');
            if (cartDropdown) cartDropdown.classList.remove('show-cart');
        });

        closeDeliveryBtn.addEventListener('click', function() {
            deliveryDropdownMenu.classList.remove('show');
        });

        document.addEventListener('click', function(event) {
            if (!deliveryDropdownMenu.contains(event.target) && !deliveryToggle.contains(event.target)) {
                if (deliveryDropdownMenu.classList.contains('show')) {
                    deliveryDropdownMenu.classList.remove('show');
                }
            }
        });
    }

    if (manageAddressBtn) {
        manageAddressBtn.addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = "{{ route('perfil') }}";
        });
    }
    if (btnLoginBtn) {
        btnLoginBtn.addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = "{{ route('login') }}";
        });
    }

    window.updateDeliveryAddressUI = function(newAddress) {
        const addressSpan = document.getElementById('currentDeliveryAddress');
        const manageBtn = document.getElementById('manageAddressBtn');
        if (addressSpan) {
            if (newAddress) {
                addressSpan.innerHTML = `Tu dirección actual: <strong>${newAddress}</strong>`;
                if (manageBtn) manageBtn.textContent = 'Cambiar Dirección';
            } else {
                addressSpan.innerHTML = `No tienes una dirección registrada.`;
                if (manageBtn) manageBtn.textContent = 'Agregar Dirección';
            }
        }
    };
});