<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}" />
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


    <section class="categorias-menu">
        {{-- Botón "Ver todo" siempre al inicio --}}
        <button class="categoria-item active" data-categoria-id="all">
            <div class="circle-texto">Ver todo</div>
            <span>Ver todo</span>
        </button>

        {{-- Iterar por las categorías --}}
        @foreach ($categorias as $categoria)
            <button class="categoria-item" data-categoria-id="{{ $categoria->id }}">
                @if ($categoria->imagen_icono)
                    <img src="{{ asset('storage/img/categorias/' . $categoria->imagen_icono) }}"
                        alt="Icono de {{ $categoria->nombre }}">
                @else
                    <img src="{{ asset('img/categoria/default.png') }}"
                        alt="Icono por defecto de {{ $categoria->nombre }}">
                @endif
                <span>{{ $categoria->nombre }}</span>
            </button>
        @endforeach
    </section>

    <main class="catalogo">
        <aside class="filtros">
            <h3>Filtrar por:</h3>
            <p><strong>Precios:</strong></p>
            <label><input type="checkbox" class="price-filter-checkbox" data-min-price="0" data-max-price="20" /> Hasta
                S/20</label>
            <label><input type="checkbox" class="price-filter-checkbox" data-min-price="21" data-max-price="30" /> S/21
                - S/30</label>
            <label><input type="checkbox" class="price-filter-checkbox" data-min-price="31" data-max-price="50" /> S/31
                - S/50</label>
            <label><input type="checkbox" class="price-filter-checkbox" data-min-price="51" data-max-price="9999" />
                S/51 +</label>
        </aside>

        <section class="productos">
            <h2>NUESTRA CARTA</h2>
            <div class="grid-productos">
                {{-- Los productos se cargarán aquí vía AJAX --}}
            </div>

            <div class="paginacion">
                {{-- La paginación se cargará aquí vía AJAX --}}
            </div>
        </section>
    </main>



    {{-- Tu JavaScript puede quedarse en el archivo principal si es específico de la página,
         o moverlo a un archivo .js externo si es global. --}}
    <script>
        window.routes = {
            productosFiltrar: "{{ route('productos.filtrar') }}",
            perfil: "{{ route('perfil') }}",
            login: "{{ route('login') }}"
            // Añade aquí cualquier otra ruta que necesites en tus JS
        };
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/products.js') }}"></script>

</body>

</html>
