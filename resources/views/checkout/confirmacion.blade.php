<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer - Confirmación de Pedido</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    {{-- Header --}}
    @include('partials.header', [
        'contador' => $contador ?? 0,
        'carritoItems' => $carritoItems ?? collect(),
        'promocionItems' => $promocionItems ?? collect(),
        'totalPrice' => $totalPrice ?? 0,
    ])

    <div class="container">
        <h2>Confirmar Pedido</h2>

        {{-- 🧾 Resumen del pedido --}}
        <ul>
            @foreach ($carritoItems as $item)
                <li>{{ $item->producto->nombre }} x{{ $item->cantidad }} — S/.
                    {{ number_format($item->producto->precio * $item->cantidad, 2) }}</li>
            @endforeach
            @foreach ($promocionItems as $promo)
                <li style="margin-bottom: 1rem;">
                    <strong>{{ $promo->promocion->nombre }}</strong> x{{ $promo->cantidad }}
                    <br>
                    <span>Precio por unidad: S/.
                        {{ number_format($promo->promocion->precio_promocional, 2) }}</span><br>
                    <span>Total: <strong>S/.
                            {{ number_format($promo->promocion->precio_promocional * $promo->cantidad, 2) }}</strong></span>

                    {{-- Detalles de los productos que componen la promoción --}}
                    @if ($promo->promocion->detalles->isNotEmpty())
                        <ul style="margin-left: 1rem; margin-top: 0.5rem;">
                            @foreach ($promo->promocion->detalles as $detalle)
                                <li>
                                    - {{ $detalle->producto->nombre }} x{{ $detalle->cantidad }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>

        @php
            $subtotal = $checkout->total;
            $delivery = 7.0;
            $totalFinal = $subtotal + $delivery;
        @endphp

        <p><strong>Subtotal:</strong> S/. {{ number_format($subtotal, 2) }}</p>
        <p><strong>Delivery:</strong> S/. {{ number_format($delivery, 2) }}</p>
        <p><strong>Total:</strong> <strong>S/. {{ number_format($totalFinal, 2) }}</strong></p>

        {{-- 📦 Formulario de confirmación --}}
        <form id="confirmar-form" method="POST" action="{{ route('checkout.confirmar', $checkout) }}">
            @csrf

            <h4>Dirección de envío</h4>

            @auth
                <label>
                    <input type="radio" name="direccion_envio" value="{{ Auth::user()->direccion }}" checked>
                    Usar mi dirección guardada: <strong>{{ Auth::user()->direccion ?? 'No registrada' }}</strong>
                </label><br>
            @endauth

            <label>
                <input type="radio" name="direccion_envio" value="" id="otraDireccionRadio">
                Ingresar nueva dirección:
            </label>
            <input type="text" name="otra_direccion" id="otraDireccionInput" class="form-control"
                placeholder="Escribe tu dirección" disabled>

            <h4 class="mt-3">Método de pago</h4>
            <select name="metodo_pago" class="form-control" required>
                <option value="">Selecciona un método</option>
                <option value="yape">Yape</option>
                <option value="plin">Plin</option>
                <option value="efectivo">Efectivo</option>
            </select    >

            <button type="submit" class="btn btn-success mt-3">Confirmar y Pagar</button>
        </form>

        <form id="cancelar-form" method="POST" action="{{ route('checkout.cancelar', $checkout) }}">
            @csrf
            <button type="submit" class="btn btn-danger mt-2">Cancelar</button>
        </form>
    </div>

    <script>
        // Habilita/deshabilita input de dirección nueva
        document.getElementById('otraDireccionRadio').addEventListener('change', function() {
            document.getElementById('otraDireccionInput').disabled = !this.checked;
        });

        // Confirmar con validación y SweetAlert2
        document.getElementById('confirmar-form').addEventListener('submit', function(e) {
            e.preventDefault();

            let direccion = document.querySelector('input[name="direccion_envio"]:checked')?.value || '';
            const nuevaDireccion = document.getElementById('otraDireccionInput').value;

            if (document.getElementById('otraDireccionRadio').checked) {
                if (!nuevaDireccion.trim()) {
                    return Swal.fire('¡Atención!', 'Debes ingresar una nueva dirección.', 'warning');
                }
                direccion = nuevaDireccion;
            }

            const metodo = document.querySelector('select[name="metodo_pago"]').value;
            if (!metodo) {
                return Swal.fire('¡Atención!', 'Selecciona un método de pago.', 'warning');
            }

            Swal.fire({
                title: '¿Confirmar pedido?',
                text: "Se generará tu orden y se procesará el pago.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'direccion_envio';
                    input.value = direccion;
                    e.target.appendChild(input);
                    e.target.submit();
                }
            });
        });

        document.getElementById('cancelar-form').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cancelar pedido?',
                text: "Perderás esta sesión de compra.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'Volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    e.target.submit();
                }
            });
        });
    </script>

    <script>
        window.routes = {
            productosFiltrar: "{{ route('productos.filtrar') }}",
            perfil: "{{ route('perfil') }}",
            login: "{{ route('login') }}"
        };
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
</body>

</html>
