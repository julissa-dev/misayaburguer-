<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel Repartidor')</title> {{-- Título por defecto 'Panel Repartidor' --}}
    
    {{-- Incluye tu CSS y JS compilados con Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    {{-- Font Awesome para los iconos (versión 6.0.0-beta3, igual que en el admin) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Estilos base para el cuerpo, usando la fuente sin-serif de Tailwind */
        body {
            font-family: sans-serif;
        }

        /* Estilos para la superposición (overlay) del sidebar en móvil */
        .sidebar-overlay {
            display: none; /* Oculto por defecto */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Fondo semitransparente */
            z-index: 30; /* Debe estar por encima del contenido principal, pero debajo del sidebar */
        }

        /* Estilos específicos para pantallas pequeñas (móviles) */
        @media (max-width: 767px) { /* Esto corresponde al breakpoint 'md' de Tailwind por defecto */
            .sidebar {
                transform: translateX(-100%); /* Oculta la sidebar completamente a la izquierda */
                transition: transform 0.3s ease-in-out; /* Animación de deslizamiento */
                position: fixed; /* Para que se superponga al contenido */
                height: 100%; /* Ocupa toda la altura de la pantalla */
                z-index: 40; /* Por encima del overlay */
            }
            .sidebar.open {
                transform: translateX(0); /* Muestra la sidebar */
            }
            /* En móvil, el contenido principal no tiene margen izquierdo fijo del sidebar */
            .main-content {
                margin-left: 0;
            }
        }

        /* Estilos para pantallas medianas y más grandes (desktop) */
        @media (min-width: 768px) { /* md breakpoint */
            .main-content {
                margin-left: 256px; /* Ancho del sidebar (w-64 = 256px) */
            }
        }
    </style>
</head>

<body class="flex bg-gray-100 font-sans min-h-screen">

    <aside id="sidebar" class="w-64 bg-gray-900 text-white flex flex-col justify-between fixed h-screen px-6 py-8 shadow-lg sidebar">
        <div>
            <h1 class="text-2xl font-bold mb-10 text-center tracking-wide">
                PANEL REPARTIDOR
            </h1>

            <nav class="space-y-3 text-sm">
                {{-- Enlace a Pedidos Asignados --}}
                <a href="{{ route('repartidor.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                 {{ request()->routeIs('repartidor.dashboard') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-boxes"></i> Pedidos Asignados
                </a>

                {{-- Enlace al Historial de Envíos --}}
                <a href="{{ route('repartidor.historial') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                 {{ request()->routeIs('repartidor.historial') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-history"></i> Historial de Envíos
                </a>
                {{-- Puedes añadir más enlaces aquí si es necesario --}}
            </nav>
        </div>

        {{-- Botón de Cerrar Sesión --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 mt-6 transition-colors">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </button>
        </form>
    </aside>

    <div id="sidebar-overlay" class="sidebar-overlay md:hidden"></div>

    <div id="main-content" class="flex-1 flex flex-col overflow-hidden main-content ml-0 md:ml-64">
        {{-- Encabezado para móviles (contiene el botón de hamburguesa y el título) --}}
        {{-- Este encabezado solo se muestra en pantallas pequeñas (oculto en md y mayores) --}}
        <header class="flex items-center p-4 bg-white border-b border-gray-200 shadow-sm md:hidden">
            <button id="sidebar-toggle" class="text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900 mr-4">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h1 class="text-xl font-semibold text-gray-800 flex-grow">@yield('titulo')</h1>
            {{-- Aquí puedes añadir el nombre de usuario si quieres, similar al admin --}}
            {{-- <span class="text-gray-700">Hola, {{ Auth::user()->nombre ?? 'Repartidor' }}</span> --}}
        </header>

        {{-- Contenido real de la página (sección donde se inyecta el @yield('contenido')) --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6 md:p-10">
            @yield('contenido')
        </main>
    </div>

    {{-- Script para la funcionalidad del menú hamburguesa --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            // Función para abrir/cerrar el sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('open');
                sidebarOverlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
            }

            // Evento click para el botón de hamburguesa
            sidebarToggle.addEventListener('click', toggleSidebar);

            // Evento click para el overlay (cierra el sidebar si se toca fuera de él)
            sidebarOverlay.addEventListener('click', toggleSidebar);

            // Cierra el sidebar automáticamente si la ventana se redimensiona a tamaño desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) { // md breakpoint de Tailwind
                    sidebar.classList.remove('open');
                    sidebarOverlay.style.display = 'none';
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>