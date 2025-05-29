<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer</title>
    <link rel="stylesheet" href="css/menu.css" />
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>
    {{-- Agrega SweetAlert2 para mensajes --}}
    
    
</head>

<body>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <header class="navbar">
        <div class="logo">
            <a href="{{ route('home') }}">
                <img src="img/front/log.png" alt="Logo del sitio" />
            </a>
        </div>

        <div class="acciones">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="¿Qué necesitas?" />
        </div>

        <button class="menu-toggle" aria-label="Abrir menú">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="nav-links">
            <a href="{{ route('menu') }}" class="{{ Request::routeIs('menu') ? 'active-link' : '' }}"><i class="fa-solid fa-burger"></i> MENU</a>
            <a href="#"><i class="fa-solid fa-percent"></i> PROMOCIONES</a>
            <a href="#"><i class="fas fa-bicycle"></i> Delivery a domicilio <i
                    class="fas fa-chevron-down"></i></a>

            @auth {{-- Si el usuario está autenticado --}}
                <div class="dropdown">
                    <a href="#"
                        class="dropdown-toggle {{ Request::routeIs('perfil') || Request::routeIs('pedido') ? 'active-link' : '' }}"
                        id="userDropdownToggle">
                        <i class="fa-solid fa-user"></i> {{ Auth::user()->nombre }} <i
                            class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-menu" id="userDropdownMenu">
                        {{-- Enlace Perfil: Activo si la ruta actual es 'profile' --}}
                        <a href="{{ route('perfil') }}"
                            class="{{ Request::routeIs('perfil') ? 'active-link' : '' }}">Perfil</a>
                        {{-- Enlace Mis Pedidos: Activo si la ruta actual es 'orders' --}}
                        <a href="{{ route('pedido') }}" class="{{ Request::routeIs('pedido') ? 'active-link' : '' }}">Mis
                            Pedidos</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">Cerrar Sesión</button>
                        </form>
                    </div>
                </div>
            @endauth


            @guest {{-- Si el usuario NO está autenticado --}}
                <a href="{{ route('login') }}"><i class="fa-solid fa-user"></i> INGRESAR</a>
            @endguest

            <button class="cart"><i class="fa-solid fa-cart-shopping"></i><span class="cart-count">0</span></button>
        </nav>
    </header>

    
    


    <script>
        const toggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav-links');

        toggle.addEventListener('click', () => {
            nav.classList.toggle('active');
        });

        // --- JavaScript para el Dropdown del Usuario ---
        const userDropdownToggle = document.getElementById('userDropdownToggle');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdownToggle && userDropdownMenu) { // Asegura que los elementos existan (si el usuario está logueado)
            userDropdownToggle.addEventListener('click', function(event) {
                event.preventDefault(); // Evita que el enlace redirija
                userDropdownMenu.classList.toggle('show'); // Alterna la clase 'show'
            });

            // Cerrar el dropdown si se hace clic fuera de él
            window.addEventListener('click', function(event) {
                if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    if (userDropdownMenu.classList.contains('show')) {
                        userDropdownMenu.classList.remove('show');
                    }
                }
            });
        }
        // --- Fin JavaScript para el Dropdown del Usuario ---


        const logoutForm = document.getElementById('logout-form');

        if (logoutForm) { // Asegúrate de que el formulario exista en la página
            logoutForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Detener el envío del formulario por defecto

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
                        // Si el usuario confirma, enviar el formulario manualmente
                        this.submit(); // 'this' se refiere al formulario 'logoutForm'
                    }
                });
            });
        }
        
    </script>
</body>

</html>
