<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer - Confirmación de Pedido</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/usuario/pagos.css') }}" />
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

    <div class="main-content">
        <div class="container confirmation-container">
            <h1 class="page-title">Confirmar Pedido</h1>

            <div class="section-card order-summary">
                <h3 class="section-title">Resumen de tu Pedido</h3>
                <ul class="order-items-list">
                    @foreach ($carritoItems as $item)
                        <li class="order-item">
                            <span class="item-name">{{ $item->producto->nombre }} x{{ $item->cantidad }}</span>
                            <span class="item-price">S/.
                                {{ number_format($item->producto->precio * $item->cantidad, 2) }}</span>
                        </li>
                    @endforeach
                    @foreach ($promocionItems as $promo)
                        <li class="order-item promo-item">
                            <span class="item-name promo-title">
                                <strong>{{ $promo->promocion->nombre }}</strong> x{{ $promo->cantidad }}
                            </span>
                            <span class="item-price promo-total">
                                <strong>S/.
                                    {{ number_format($promo->promocion->precio_promocional * $promo->cantidad, 2) }}</strong>
                            </span>
                            <br>
                            <span class="promo-unit-price">Precio por unidad: S/.
                                {{ number_format($promo->promocion->precio_promocional, 2) }}</span>

                            @if ($promo->promocion->detalles->isNotEmpty())
                                <ul class="promo-details-list">
                                    @foreach ($promo->promocion->detalles as $detalle)
                                        <li>- {{ $detalle->producto->nombre }} x{{ $detalle->cantidad }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @php
                    $subtotal = $checkout->total;
                    $delivery = 7.0; // Este valor deberías enviarlo desde el backend para mayor flexibilidad
                    $totalFinal = $subtotal + $delivery;
                @endphp

                <div class="summary-totals">
                    <p class="summary-line"><span>Subtotal:</span> <span class="price-value">S/.
                            {{ number_format($subtotal, 2) }}</span></p>
                    <p class="summary-line"><span>Delivery:</span> <span class="price-value">S/.
                            {{ number_format($delivery, 2) }}</span></p>
                    <p class="summary-line total-line"><span>Total:</span> <span class="price-value">S/.
                            {{ number_format($totalFinal, 2) }}</span></p>
                </div>
            </div> {{-- Fin .section-card --}}

            <form id="confirmar-form" method="POST" enctype="multipart/form-data"
                action="{{ route('checkout.confirmar', $checkout) }}">
                @csrf

                <div class="section-card shipping-address">
                    <h3 class="section-title">Dirección de envío</h3>

                    @auth
                        <div class="radio-option">
                            <input type="radio" name="direccion_envio" value="{{ Auth::user()->direccion }}"
                                id="currentAddressRadio" checked>
                            <label for="currentAddressRadio">Usar mi dirección guardada:
                                <strong>{{ Auth::user()->direccion ?? 'No registrada' }}</strong></label>
                        </div>
                    @endauth

                    <div class="radio-option">
                        <input type="radio" name="direccion_envio" value="" id="otraDireccionRadio">
                        <label for="otraDireccionRadio">Ingresar nueva dirección:</label>
                    </div>
                    <input type="text" name="otra_direccion" id="otraDireccionInput" class="text-input"
                        placeholder="Escribe tu dirección" disabled>
                </div> {{-- Fin .section-card --}}

                <div class="section-card payment-method">
                    <h3 class="section-title">Método de pago</h3>
                    <select name="metodo_pago" class="select-input" required>
                        <option value="">Selecciona un método</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                        {{-- <option value="efectivo">Efectivo</option> --}}
                    </select>

                    <div id="qr-container" class="qr-payment-section" style="display: none;">
                        <p class="qr-instruction">Escanea el código QR con tu app:</p>
                        <img id="qr-image" src="" alt="QR Yape o Plin" class="qr-code-img" />
                    </div>

                    <div id="comprobante-container" class="file-upload-section" style="display: none;">
                        <label for="comprobante" class="file-label">Sube el comprobante de Yape o Plin (imagen):</label>
                        <input type="file" name="comprobante" id="comprobante" accept="image/*" class="file-input">
                    </div>
                </div> {{-- Fin .section-card --}}

                <button type="submit" class="btn btn-confirm">Confirmar y Pagar</button>
            </form>

            <div class="section-card paypal-section">
                <h3 class="section-title">Pagar con PayPal</h3>
                <div id="paypal-button-container"></div>
            </div>

            <form id="cancelar-form" method="POST" action="{{ route('checkout.cancelar', $checkout) }}">
                @csrf
                <button type="submit" class="btn btn-cancel">Cancelar Pedido</button>
            </form>
        </div> {{-- Fin .confirmation-container --}}
    </div> {{-- Fin .main-content --}}

    {{-- Script de PayPal --}}
    <script
        src="https://www.paypal.com/sdk/js?client-id=AVuKm9qi6S9JfyqmNZ4xmOdJ6m8SHMKPA-e03btcWPZmq8Z44G2FETkWwbnYIYXE9HQwazDqCHJiudtt">
    </script>

    <script>
        const totalFinalPen = {{ number_format($totalFinalPen, 2, '.', '') }};
        const totalFinalUsd = {{ number_format($totalFinalUsd, 2, '.', '') }};
        const checkoutId = {{ $checkout->id ?? 'null' }};

        console.log('Total PEN:', totalFinalPen);
        console.log('Total USD para PayPal:', totalFinalUsd);

        paypal.Buttons({
            fundingSource: paypal.FUNDING.PAYPAL,
            createOrder: function(data, actions) {
                return actions.order.create({
                    application_context: {
                        shipping_preference: 'NO_SHIPPING'
                    },
                    purchase_units: [{
                        amount: {
                            value: totalFinalUsd.toFixed(2)
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                const orderID = data.orderID;

                return fetch(`/paypal/confirmar-pedido/${orderID}/${checkoutId}`)
                    .then(res => {
                        const contentType = res.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            return res.text().then(text => {
                                throw new Error("Respuesta no válida: " + text);
                            });
                        }
                        return res.json();
                    })
                    .then(result => {
                        if (result.success) {
                            Swal.fire('¡Éxito!', 'Pedido registrado con PayPal.', 'success')
                                .then(() => window.location.href = "/mis-pedidos");
                        } else {
                            Swal.fire('Error', result.message || 'Hubo un problema al confirmar el pedido.',
                                'error');
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        Swal.fire('Error', error.message, 'error');
                    });
            },
            onError: function(err) {
                console.error('Error en el pago:', err);
                Swal.fire('¡Error!', 'Ocurrió un error al procesar el pago. Inténtalo de nuevo.', 'error');
            }
        }).render('#paypal-button-container');
    </script>

    <script>
        document.getElementById('otraDireccionRadio').addEventListener('change', function() {
            document.getElementById('otraDireccionInput').disabled = !this.checked;
            document.getElementById('otraDireccionInput').focus();
        });

        document.getElementById('currentAddressRadio')?.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('otraDireccionInput').disabled = true;
                document.getElementById('otraDireccionInput').value = '';
            }
        });

        document.querySelector('select[name="metodo_pago"]').addEventListener('change', function() {
            const metodo = this.value;
            const comprobanteDiv = document.getElementById('comprobante-container');
            const qrContainer = document.getElementById('qr-container');
            const qrImage = document.getElementById('qr-image');

            comprobanteDiv.style.display = 'none';
            qrContainer.style.display = 'none';
            qrImage.src = '';
            document.getElementById('comprobante').removeAttribute('required');

            if (metodo === 'yape') {
                comprobanteDiv.style.display = 'block';
                qrContainer.style.display = 'flex';
                qrImage.src = "{{ asset('img/qr/yape-qr.png') }}";
                document.getElementById('comprobante').setAttribute('required', 'required');
            } else if (metodo === 'plin') {
                comprobanteDiv.style.display = 'block';
                qrContainer.style.display = 'flex';
                qrImage.src = "{{ asset('img/qr/plin-qr.png') }}";
                document.getElementById('comprobante').setAttribute('required', 'required');
            }
        });

        document.getElementById('confirmar-form').addEventListener('submit', function(e) {
            e.preventDefault();

            let direccion = document.querySelector('input[name="direccion_envio"]:checked')?.value || '';
            const nuevaDireccion = document.getElementById('otraDireccionInput').value;

            if (document.getElementById('otraDireccionRadio').checked) {
                if (!nuevaDireccion.trim()) {
                    return Swal.fire('¡Atención!', 'Debes ingresar una nueva dirección.', 'warning');
                }
                direccion = nuevaDireccion;
            } else if (document.getElementById('currentAddressRadio')?.checked) {
                if (!direccion.trim()) {
                    return Swal.fire('¡Atención!',
                        'No tienes una dirección guardada en tu perfil. Por favor ingrésala manualmente.',
                        'warning');
                }
            } else {
                return Swal.fire('¡Atención!', 'Por favor, selecciona o ingresa una dirección de envío.',
                'warning');
            }

            const metodo = document.querySelector('select[name="metodo_pago"]').value;
            if (!metodo) {
                return Swal.fire('¡Atención!', 'Selecciona un método de pago.', 'warning');
            }

            if (metodo === 'yape' || metodo === 'plin') {
                const fileInput = document.getElementById('comprobante');
                const file = fileInput.files[0];

                if (!file) {
                    return Swal.fire('¡Comprobante requerido!', 'Debes subir una imagen del pago por ' + metodo
                        .toUpperCase(), 'warning');
                }

                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    return Swal.fire('Formato inválido', 'Solo se permiten imágenes JPG o PNG.', 'error');
                }

                const maxSizeMB = 2;
                if (file.size > maxSizeMB * 1024 * 1024) {
                    return Swal.fire('Archivo muy grande', `El comprobante no debe superar ${maxSizeMB} MB.`,
                        'error');
                }
            }

            Swal.fire({
                title: '¿Confirmar pedido?',
                text: "Se generará tu orden y se procesará el pago.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Volver',
                customClass: {
                    confirmButton: 'swal2-confirm-button',
                    cancelButton: 'swal2-cancel-button'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const hiddenDireccionInput = document.createElement('input');
                    hiddenDireccionInput.type = 'hidden';
                    hiddenDireccionInput.name = 'direccion_envio';
                    hiddenDireccionInput.value = direccion;
                    e.target.appendChild(hiddenDireccionInput);
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
                cancelButtonText: 'Volver',
                customClass: {
                    confirmButton: 'swal2-confirm-button',
                    cancelButton: 'swal2-cancel-button'
                }
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
