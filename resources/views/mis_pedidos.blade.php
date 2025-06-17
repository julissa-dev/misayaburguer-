<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer</title>
    <link rel="stylesheet" href="{{ asset('css/usuario/perfil.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    {{-- Incluye la plantilla del header --}}
    @include('partials.header', [
        'contador' => $contador ?? 0,
        'carritoItems' => $carritoItems ?? collect(),
        'promocionItems' => $promocionItems ?? collect(), // <--- Agrega esta línea
        'totalPrice' => $totalPrice ?? 0,
    ])


    @foreach ($pedidos as $pedido)
        <div class="pedido">
            <p>Pedido #{{ $pedido->id }} — {{ $pedido->fecha->format('d/m/Y') }}</p>
            <p>Total: S/. {{ number_format($pedido->total, 2) }}</p>
            <p>Estado: {{ $pedido->estado }}</p>
            <button class="btn btn-info" onclick="verDetallePedido({{ $pedido->id }})">Ver Detalles</button>

        </div>
    @endforeach

    <h3>Pedidos no finalizados</h3>
    @foreach ($checkoutsPendientes as $checkout)
        <div class="checkout-pendiente">
            <p>Monto: S/. {{ number_format($checkout->total, 2) }}</p>
            <p>Creado: {{ $checkout->created_at->format('d/m/Y H:i') }}</p>
            <a href="{{ route('checkout.confirmacion', $checkout) }}">Continuar pedido</a>
        </div>
    @endforeach

    <div class="modal" id="modalDetallePedido" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h3>Detalles del Pedido</h3>
            <div id="contenidoPedido">
                <p>Cargando...</p>
            </div>
        </div>
    </div>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
    </style>

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

    <script>
        function verDetallePedido(pedidoId) {
            fetch(`/api/pedidos/${pedidoId}`)
                .then(res => res.json())
                .then(data => {
                    const div = document.getElementById('contenidoPedido');
                    if (!data.success) {
                        div.innerHTML = '<p>No se pudo cargar el pedido.</p>';
                        return;
                    }

                    const pedido = data.pedido;
                    let html = `<p><strong>Fecha:</strong> ${pedido.fecha}</p>`;
                    html += `<p><strong>Estado:</strong> ${pedido.estado}</p>`;
                    html += `<p><strong>Dirección:</strong> ${pedido.direccion}</p>`;
                    html += '<h4>Productos:</h4><ul>';

                    pedido.items.forEach(item => {
                        html += `<li>${item.producto.nombre} x${item.cantidad} — S/. ${item.precio_unit}</li>`;
                    });

                    html += '</ul>';
                    html += `<p><strong>Total:</strong> S/. ${pedido.total}</p>`;

                    if (pedido.pago && pedido.pago.referencia) {
                        html += `<p><strong>Pago:</strong> ${pedido.pago.metodo}</p>`;
                        if (pedido.pago.metodo === 'yape' || pedido.pago.metodo === 'plin') {
                            html +=
                                `<img src="/storage/${pedido.pago.referencia}" style="max-width: 100%; margin-top: 1rem;" alt="Comprobante" />`;
                        }
                    }

                    document.getElementById('modalDetallePedido').style.display = 'flex';
                    div.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('contenidoPedido').innerHTML = 'Error al cargar.';
                });
        }

        function cerrarModal() {
            document.getElementById('modalDetallePedido').style.display = 'none';
        }
    </script>
</body>

</html>
