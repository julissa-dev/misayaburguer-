<header class="navbar">
    <div class="logo">
        <a href="{{ route('home') }}">
            <img src="img/front/log.png" alt="Logo del sitio" />
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
        <a href="#"><i class="fas fa-bicycle"></i> Delivery a domicilio <i class="fas fa-chevron-down"></i></a>
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
