<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Promociones | Saya Burguer</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/promocion.css') }}" />
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    {{-- HEADER --}}
    {{-- Incluye la plantilla del header --}}
    @include('partials.header', [
        'contador' => $contador ?? 0,
        'carritoItems' => $carritoItems ?? collect(),
        'promocionItems' => $promocionItems ?? collect(), // <--- Agrega esta línea
        'totalPrice' => $totalPrice ?? 0,
    ])

    <main class="promociones-container">
        <h1 class="titulo-promos">Promociones Disponibles</h1>

        @if ($promociones->isEmpty())
            <p>No hay promociones disponibles en este momento.</p>
        @else
            <div class="promo-grid">
                @foreach ($promociones as $promo)
                    <div class="promo-card">
                        <img src="{{ asset('storage/img/promociones/' . $promo->imagen_url) }}" alt="{{ $promo->nombre }}">
                        <h2>{{ $promo->nombre }}</h2>
                        <p>{{ $promo->descripcion }}</p>
                        <p class="promo-precio">S/ {{ number_format($promo->precio_promocional, 2) }}</p>

                        {{-- Mostrar productos incluidos en esta promo --}}
                        @if ($promo->detalles && $promo->detalles->count())
                            <ul class="productos-incluidos">
                                @foreach ($promo->detalles as $detalle)
                                    <li>
                                        {{ $detalle->producto->nombre ?? 'Producto no disponible' }}
                                        x{{ $detalle->cantidad }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <button class="btn-add-to-cart-promo" data-promocion-id="{{ $promo->id }}">
                            <i class="fa-solid fa-cart-plus"></i> Añadir al Carrito
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <script>
        document.addEventListener("click", async function (event) {
            const target = event.target;

            if (target.classList.contains("btn-add-to-cart-promo")) {
                const promoId = target.dataset.promocionId;

                try {
                    const response = await fetch("/carrito/promocion/añadir", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({
                            promocion_id: promoId,
                            cantidad: 1
                        }),
                    });

                    const data = await response.json();
                    if (data.success) {
                        window.updateCartUI(data);
                        Swal.fire("Añadido", data.message, "success");
                    } else {
                        Swal.fire("Error", data.message, "error");
                    }
                } catch (error) {
                    console.error("Error al añadir promoción:", error);
                    Swal.fire("Error", "Hubo un problema al añadir la promoción.", "error");
                }
            }
        });
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
</body>
</html>
