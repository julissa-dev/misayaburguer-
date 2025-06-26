<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>@yield('titulo', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="flex bg-gray-100 font-sans min-h-screen">
    <aside class="w-64 bg-gray-900 text-white flex flex-col justify-between fixed h-screen px-6 py-8">
        <div>
            <h1 class="text-2xl font-bold mb-10 text-center tracking-wide">
                ADMINISTRADOR
            </h1>

            <nav class="space-y-3 text-sm">
                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>

                {{-- Pedidos --}}
                <a href="{{ route('admin.pedidos.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                 {{ request()->routeIs('admin.pedidos.*') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-box"></i> Pedidos
                </a>

                {{-- Productos --}}
                <a href="{{ route('admin.productos.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                 {{ request()->routeIs('admin.productos.*') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-pizza-slice"></i> Productos
                </a>

                {{-- Promociones --}}
                <a href="{{ route('admin.promociones.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                 {{ request()->routeIs('admin.promociones.*') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-tags"></i> Promociones
                </a>


                {{-- Usuarios --}}
                <a href="{{ route('admin.usuarios.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                 {{ request()->routeIs('admin.usuarios.*') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-users"></i> Usuarios
                </a>

                
            </nav>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 mt-6 transition-colors">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </button>
        </form>
    </aside>

    <main class="ml-64 p-6 md:p-10 w-full">
        @yield('contenido')
    </main>
</body>

</html>