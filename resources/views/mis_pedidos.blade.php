<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mis Pedidos - Saya Burguer</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/usuario/mis_pedidos.css') }}" />
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    {{-- Incluye la plantilla del header --}}
    @include('partials.header', [
        'contador' => $contador ?? 0,
        'carritoItems' => $carritoItems ?? collect(),
        'promocionItems' => $promocionItems ?? collect(),
        'totalPrice' => $totalPrice ?? 0,
    ])

    <main class="mis-pedidos-container">
        <h1>Mis Pedidos</h1>

        @forelse ($pedidos as $pedido)
            <div class="pedido-card estado-{{ Str::slug($pedido->estado) }}">
                <div class="pedido-header">
                    <p class="pedido-id">Pedido #{{ $pedido->id }}</p>
                    {{-- Eliminado ->setTimezone('America/Lima') aquí. Carbon ya lo maneja por config/app.php --}}
                    <p class="pedido-date">{{ $pedido->fecha->format('d/m/Y h:i A') }}</p>

                </div>
                <div class="pedido-body">
                    <p>Total: <span class="pedido-total">S/. {{ number_format($pedido->total, 2) }}</span></p>
                    <p>Estado: <span
                            class="pedido-estado">{{ $pedido->estado_label ?? ucfirst($pedido->estado) }}</span></p>
                    <div class="pedido-actions">
                        <button class="btn btn-primary" onclick="verDetallePedido({{ $pedido->id }})">Ver
                            Detalles</button>
                        @if ($pedido->envio && in_array($pedido->envio->estado, ['asignado', 'en ruta']))
                            <button class="btn btn-secondary" onclick="verSeguimientoEnvio({{ $pedido->id }})">
                                <i class="fas fa-motorcycle"></i> Seguimiento Envío
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-box-open empty-icon"></i>
                <p>Aún no tienes pedidos realizados. ¡Anímate a explorar nuestro menú!</p>
                <a href="{{ route('menu') }}" class="btn btn-primary">Ver Menú</a>
            </div>
        @endforelse

    </main>

    {{-- Modal de Detalles de Pedido --}}
    <div class="modal-overlay" id="modalDetallePedido">
        <div class="modal-content">
            <span class="close-button" onclick="cerrarModal('modalDetallePedido')">&times;</span>
            <h3 class="modal-title">Detalles del Pedido <span id="modal-pedido-id"></span></h3>
            <div id="contenidoPedido" class="modal-body">
                <p>Cargando...</p>
            </div>
        </div>
    </div>

    {{-- Modal de Seguimiento de Envío --}}
    <div class="modal-overlay" id="modalSeguimientoEnvio">
        <div class="modal-content">
            <span class="close-button" onclick="cerrarModal('modalSeguimientoEnvio')">&times;</span>
            <h3 class="modal-title">Seguimiento de tu Pedido <span id="modal-seguimiento-id"></span></h3>
            <div id="contenidoSeguimiento" class="modal-body">
                <p>Cargando seguimiento...</p>
            </div>
        </div>
    </div>


    <script>
        window.routes = {
            productosFiltrar: "{{ route('productos.filtrar') }}",
            perfil: "{{ route('perfil') }}",
            login: "{{ route('login') }}"
        };
        const PRECIO_ENVIO = 7.00; // Define el precio del envío aquí
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
    <script>
        // Función para formatear números como moneda
        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        // Función para abrir el modal
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        // Función para cerrar un modal específico
        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Función para mostrar SweetAlerts
        function showSweetAlert(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#e74c3c'
            });
        }

        async function verDetallePedido(pedidoId) {
            abrirModal('modalDetallePedido');
            document.getElementById('modal-pedido-id').textContent = '#' + pedidoId;
            document.getElementById('contenidoPedido').innerHTML = '<p>Cargando detalles del pedido...</p>';

            try {
                const res = await fetch(`/api/pedidos/${pedidoId}`);
                const data = await res.json();

                const divContenido = document.getElementById('contenidoPedido');
                if (!data.success || !data.pedido) {
                    divContenido.innerHTML = '<p>No se pudo cargar el pedido. Intente nuevamente.</p>';
                    showSweetAlert('error', 'Error al cargar', 'No se encontraron los detalles del pedido.');
                    return;
                }

                const pedido = data.pedido;
                let html = '';
                // Mantenido timeZone: 'America/Lima' aquí, ya que JavaScript se ejecuta en el cliente
                let fecha = new Date(pedido.fecha).toLocaleString('es-PE', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    timeZone: 'America/Lima'
                });

                // Calcular subtotal
                let subtotal = 0;
                pedido.items.forEach(item => {
                    // Asegúrate de que item.promocion exista antes de acceder a sus propiedades
                    if (item.promocion) {
                        // Verifica si total_precio_promo es una propiedad válida en tu modelo/JSON
                        subtotal += (item.promocion.total_precio_promo || item.precio_unit) * item.cantidad;
                    } else {
                        subtotal += item.precio_unit * item.cantidad;
                    }
                });

                html += `<p><strong>Fecha del pedido:</strong> ${fecha}</p>`;
                html +=
                    `<p><strong>Estado:</strong> <span class="pedido-estado-badge estado-${pedido.estado.toLowerCase().replace(/ /g, '-') }">${pedido.estado_label ?? pedido.estado}</span></p>`;
                html += `<p><strong>Dirección:</strong> ${pedido.direccion}</p>`;

                // Método de pago
                // Se espera que pedido.pago sea un objeto, no una colección aquí, ya que es 1:1.
                // Si la relación en el API devuelve un array, toma el primer elemento.
                if (pedido.pago) { // Ya no verificamos .length > 0 si es un solo objeto
                    // Si pedido.pago puede ser un array (aunque Laravel lo mapea a objeto en hasOne),
                    // asegúrate de acceder al objeto correcto. En el API, si haces $pedido->load('pago'),
                    // y pago es hasOne, será un objeto directamente.
                    const pago = Array.isArray(pedido.pago) ? pedido.pago[0] : pedido.pago;

                    if (pago) { // Verifica que el objeto pago existe
                        const metodo = pago.metodo.charAt(0).toUpperCase() + pago.metodo.slice(1);
                        html += `<p><strong>Método de Pago:</strong> ${metodo}</p>`;
                    } else {
                        html += `<p><strong>Método de Pago:</strong> No especificado</p>`;
                    }

                } else {
                    html += `<p><strong>Método de Pago:</strong> No especificado</p>`;
                }


                // Items
                html += '<h4>Items del Pedido:</h4>';
                html += '<ul class="pedido-items-list">';

                const groupedItems = {};
                pedido.items.forEach(item => {
                    if (item.promocion_id) {
                        if (!groupedItems[item.promocion_id]) {
                            groupedItems[item.promocion_id] = {
                                promocion: item.promocion,
                                cantidad_promo: item.cantidad, // Cantidad de la promoción en el pedido
                                items: []
                            };
                        }
                        groupedItems[item.promocion_id].items.push(item);
                    } else {
                        const productId = `producto-${item.producto ? item.producto.id : 'desconocido'}`;
                        if (!groupedItems[productId]) {
                            groupedItems[productId] = {
                                promocion: null,
                                items: []
                            };
                        }
                        groupedItems[productId].items.push(item);
                    }
                });

                for (const groupId in groupedItems) {
                    const group = groupedItems[groupId];
                    if (group.promocion) {
                        const promocion = group.promocion;
                        // Asegúrate de que promocion.total_precio_promo sea el campo correcto
                        const promoTotalPrice = promocion.total_precio_promo !== undefined ? promocion.total_precio_promo : (promocion.precio_promocional || 0);

                        html += `<li class="promo-item">
                                <strong>🎁 ${promocion.nombre ?? 'Promoción Desconocida'}</strong> x${group.cantidad_promo}
                                <span class="item-price">S/. ${number_format(promoTotalPrice * group.cantidad_promo, 2)}</span>
                                <ul class="promo-sub-items">`;
                        // Asegúrate de que 'detalles' existe en la promoción
                        if (promocion.detalles && Array.isArray(promocion.detalles)) {
                            promocion.detalles.forEach(detalle => {
                                html +=
                                    `<li>- ${detalle.producto ? detalle.producto.nombre : 'Producto Desconocido'} x${detalle.cantidad}</li>`;
                            });
                        }
                        html += `</ul></li>`;
                    } else {
                        group.items.forEach(item => {
                            html += `<li class="product-item">
                                <span>${item.producto ? item.producto.nombre : 'Producto Desconocido'}</span>
                                <span>x${item.cantidad}</span>
                                <span class="item-price">S/. ${number_format(item.precio_unit * item.cantidad, 2)}</span>
                                </li>`;
                        });
                    }
                }

                html += '</ul>';

                // Mostrar Subtotal y Costo de Envío
                html += `<div class="order-summary">
                                <p><strong>Subtotal:</strong> <span class="summary-price">S/. ${number_format(subtotal, 2)}</span></p>
                                <p><strong>Costo de Envío:</strong> <span class="summary-price">S/. ${number_format(PRECIO_ENVIO, 2)}</span></p>
                            </div>`;


                html +=
                    `<p class="modal-total"><strong>Total a pagar:</strong> <span class="total-price">S/. ${number_format(pedido.total, 2)}</span></p>`;

                divContenido.innerHTML = html;

            } catch (err) {
                console.error("Error al cargar detalles del pedido:", err);
                document.getElementById('contenidoPedido').innerHTML =
                    '<p>Error al cargar los detalles del pedido. Por favor, inténtelo de nuevo más tarde.</p>';
                showSweetAlert('error', 'Error de conexión', 'No se pudieron obtener los detalles del pedido.');
            }
        }

        async function verSeguimientoEnvio(pedidoId) {
            abrirModal('modalSeguimientoEnvio');
            document.getElementById('modal-seguimiento-id').textContent = '#' + pedidoId;
            document.getElementById('contenidoSeguimiento').innerHTML = '<p>Cargando seguimiento del envío...</p>';

            try {
                const res = await fetch(`/api/pedidos/${pedidoId}`);
                const data = await res.json();

                const divContenido = document.getElementById('contenidoSeguimiento');
                if (!data.success || !data.pedido || !data.pedido.envio) {
                    divContenido.innerHTML = '<p>No hay información de seguimiento disponible para este pedido.</p>';
                    showSweetAlert('info', 'Sin seguimiento',
                        'Este pedido aún no tiene información de seguimiento o ya fue entregado.');
                    return;
                }

                const envio = data.pedido.envio;
                const estadosEnvio = [{
                        key: 'asignado',
                        label: 'Asignado',
                        icon: '<i class="fas fa-user-check fa-lg"></i>'
                    },
                    {
                        key: 'en-ruta', // Este key coincide con el slug 'en-ruta'
                        label: 'En Ruta',
                        icon: '<i class="fas fa-motorcycle fa-lg"></i>'
                    },
                    {
                        key: 'entregado',
                        label: 'Entregado',
                        icon: '<i class="fas fa-box-open fa-lg"></i>'
                    }
                ];

                let html = `
            <p><strong>Estado del Envío:</strong>
                <span class="envio-estado-badge estado-${envio.estado.toLowerCase().replace(/ /g, '-') }">
                    ${envio.estado_label ?? envio.estado}
                </span>
            </p>
            <p><strong>Última actualización:</strong>
                ${new Date(envio.updated_at).toLocaleString('es-PE', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: 'America/Lima' })}
            </p>
        `;

                // Seguimiento visual tipo Rappi
                const currentIndex = estadosEnvio.findIndex(e => e.key === envio.estado.toLowerCase().replace(/ /g,
                    '-'));
                html += `<div class="envio-tracking-rappi">`;
                estadosEnvio.forEach((estado, index) => {
                    let stepStatus = '';
                    if (index < currentIndex) stepStatus = 'completado';
                    else if (index === currentIndex) stepStatus = 'activo';
                    else stepStatus = 'pendiente';

                    html += `
                <div class="tracking-step-rappi ${stepStatus}">
                    <div class="circle-rappi">${estado.icon}</div>
                    <span class="label">${estado.label}</span>
                </div>
            `;
                    if (index < estadosEnvio.length - 1) {
                        html += `<div class="linea-rappi ${stepStatus}"></div>`;
                    }
                });
                html += `</div>`;

                // Mensaje contextual
                html += '<div class="tracking-message">';
                switch (envio.estado.toLowerCase().replace(/ /g, '-')) {
                    case 'asignado':
                        html +=
                            `<p><i class="fas fa-info-circle"></i> Tu pedido está siendo preparado y pronto será enviado.</p>`;
                        break;
                    case 'en-ruta': // Asegúrate de que coincida con 'en ruta' en la DB
                        html +=
                            `<p><i class="fas fa-truck-moving"></i> ¡El repartidor está en camino a tu dirección!</p>`;
                        break;
                    case 'entregado':
                        html += `<p><i class="fas fa-check-circle"></i> ¡Tu pedido fue entregado! ¡Buen provecho!</p>`;
                        break;
                    default:
                        html +=
                            `<p><i class="fas fa-question-circle"></i> Estado actual: ${envio.estado_label ?? envio.estado}</p>`;
                }
                html += '</div>';

                divContenido.innerHTML = html;

            } catch (err) {
                console.error("Error al cargar seguimiento del envío:", err);
                document.getElementById('contenidoSeguimiento').innerHTML =
                    '<p>Error al cargar el seguimiento del envío. Por favor, inténtelo de nuevo más tarde.</p>';
                showSweetAlert('error', 'Error de conexión', 'No se pudo obtener el seguimiento del pedido.');
            }
        }
    </script>
</body>

</html>