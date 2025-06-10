<header class="navbar">
    <div class="logo">
        <a href="{{ route('home') }}">
            <img src="{{ asset('img/front/log.png') }}" alt="Logo del sitio" />
        </a>
    </div>

    <div class="acciones">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="¿Qué necesitas?" autocomplete="off" />
        <div id="searchResults" class="search-results-dropdown">
            {{-- Los resultados de la búsqueda se inyectarán aquí --}}
        </div>
    </div>

    <button class="menu-toggle" aria-label="Abrir menú">
        <i class="fa-solid fa-bars"></i>
    </button>

    <nav class="nav-links">
        <a href="{{ route('menu') }}" class="{{ Request::routeIs('menu') ? 'active-link' : '' }}">
            <i class="fa-solid fa-burger"></i> MENU
        </a>
        <a href="#"><i class="fa-solid fa-percent"></i> PROMOCIONES</a>
        {{-- INICIO: Dropdown de Delivery a Domicilio --}}
        <div class="dropdown"> {{-- Reutilizamos la clase 'dropdown' que ya tienes --}}
            <a href="#" id="deliveryToggle" class="dropdown-toggle"> {{-- Usamos dropdown-toggle --}}
                <i class="fas fa-bicycle"></i> Delivery a domicilio <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>
            <div class="dropdown-menu" id="deliveryDropdownMenu"> {{-- Usamos dropdown-menu --}}
                <div class="delivery-dropdown-header">
                    <h3>Información de Envío</h3>
                    <button class="close-delivery-btn" id="closeDeliveryBtn">&times;</button>
                </div>

                <div class="delivery-dropdown-content">
                    @auth
                        {{-- La dirección actual del usuario --}}
                        <p class="user-delivery-address">
                            @if (Auth::user()->direccion)
                                <i class="fas fa-map-marker-alt"></i> Tu dirección actual: <br> <strong id="currentDeliveryAddress">{{ Auth::user()->direccion }}</strong>
                            @else
                                <i class="fas fa-exclamation-triangle"></i> <span id="currentDeliveryAddress">No tienes una dirección registrada.</span>
                            @endif
                        </p>
                        <button class="btn-manage-address" id="manageAddressBtn">
                            @if (Auth::user()->direccion)
                                Cambiar Dirección
                            @else
                                Agregar Dirección
                            @endif
                        </button>
                    @endauth
                    @guest
                        <p class="no-address">
                            <i class="fas fa-info-circle"></i> Inicia sesión para gestionar tu dirección de envío.
                        </p>
                        <button class="btn-manage-address" id="btnLoginBtn">
                            Iniciar Sesión
                        </button>
                    @endguest

                    <hr>

                    <div class="delivery-info-section">
                        <h4>Detalles del Envío en Trujillo</h4>
                        <p><i class="fas fa-money-bill-wave"></i> Costo de Envío: <span class="delivery-price">S/ 7.00</span></p> {{-- Precio fijo --}}
                        <p><i class="fas fa-clock"></i> Horario de Delivery: <span class="delivery-time">Lunes a Domingo: 6:00 PM - 1:00 AM</span></p>
                        <p><i class="fas fa-motorcycle"></i> Tiempo Estimado: <span class="delivery-estimate">30 - 45 minutos (puede variar según la zona y la demanda)</span></p>
                        <p><i class="fas fa-dollar-sign"></i> Cancelar antes del envío</p>
                        <p class="small-text">
                            <i class="fas fa-exclamation-triangle"></i> Zonas de Cobertura: Nuestro servicio de delivery cubre todo Trujillo.
                        </p>
                    </div>

                    
                    
                </div>
            </div>
        </div>
        {{-- FIN: Dropdown de Delivery a Domicilio --}}
        
        @auth {{-- Si el usuario está autenticado --}}
            <div class="dropdown">
                <a href="#"
                    class="dropdown-toggle {{ Request::routeIs('perfil') || Request::routeIs('pedido') ? 'active-link' : '' }}"
                    id="userDropdownToggle">
                    <i class="fa-solid fa-user"></i> {{ Auth::user()->nombre }} <i
                        class="fas fa-chevron-down dropdown-arrow"></i>
                </a>
                <div class="dropdown-menu" id="userDropdownMenu">
                    <a href="{{ route('perfil') }}" class="{{ Request::routeIs('perfil') ? 'active-link' : '' }}">Perfil
                    </a>
                    <a href="{{ route('pedido') }}" class="{{ Request::routeIs('pedido') ? 'active-link' : '' }}">Mis
                        Pedidos
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">Cerrar Sesión</button>
                    </form>
                </div>
            </div>
            <button class="cart" id="cartButton">
                <i class="fa-solid fa-cart-shopping"></i><span class="cart-count">{{ $contador }}</span>
            </button>

            <div class="cart-dropdown" id="cartDropdown">
                <div class="cart-dropdown-header">
                    <h3>Tu Carrito</h3>
                    <button class="close-cart-btn" id="closeCartBtn">&times;</button>
                </div>
                <div class="cart-dropdown-items" id="cartDropdownItems">
                    {{-- Aquí se incluirá el contenido de los ítems del carrito. --}}
                    @include('partials.cart_items', ['carritoItems' => $carritoItems])
                </div>
                <div class="cart-dropdown-footer">
                    <span class="total-label">Total:</span>
                    <span class="total-price" id="cartTotalPrice">${{ number_format($totalPrice ?? 0, 2) }}</span>
                    <a href="#" class="btn-checkout">Ir a Pagar</a>
                </div>
            </div>
        @endauth
        @guest {{-- Si el usuario NO está autenticado (es un invitado) --}}
            <a href="{{ route('login') }}"><i class="fa-solid fa-user"></i> INGRESAR</a>
            <button class="cart"><i class="fa-solid fa-cart-shopping"></i><span class="cart-count">0</span></button>
        @endguest
    </nav>
</header>
