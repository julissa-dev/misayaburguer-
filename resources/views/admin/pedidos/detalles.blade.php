@extends('layouts.admin')

@section('titulo', 'Detalles del Pedido #' . $pedido->id)

@section('contenido')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Detalles del Pedido #{{ $pedido->id }}</h2>
        <a href="{{ route('admin.pedidos.index') }}" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200 flex items-center gap-2">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Pedidos
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Información General del Pedido --}}
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-receipt mr-3 text-blue-500"></i> Información del Pedido
                </h3>
                <div class="space-y-2 text-gray-800">
                    <p><strong class="text-gray-600">Total:</strong> S/ {{ number_format($pedido->total, 2) }}</p>
                    <p><strong class="text-gray-600">Estado del Pedido:</strong> <span class="capitalize font-medium">{{ $pedido->estado }}</span></p>
                    <p><strong class="text-gray-600">Fecha del Pedido:</strong> {{ $pedido->fecha->format('d/m/Y H:i') }}</p>
                    <p><strong class="text-gray-600">Dirección de Envío:</strong> {{ $pedido->direccion }}</p>
                </div>
            </div>

            {{-- Información del Cliente --}}
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-user-circle mr-3 text-purple-500"></i> Información del Cliente
                </h3>
                <div class="space-y-2 text-gray-800">
                    @if($pedido->usuario)
                        <p><strong class="text-gray-600">Nombre:</strong> {{ $pedido->usuario->nombre }} {{ $pedido->usuario->apellido }}</p>
                        <p><strong class="text-gray-600">Email:</strong> {{ $pedido->usuario->email }}</p>
                        <p><strong class="text-gray-600">Teléfono:</strong> {{ $pedido->usuario->telefono ?? 'N/A' }}</p>
                    @else
                        <p class="text-gray-500 italic">Cliente no disponible.</p>
                    @endif
                </div>
            </div>

            {{-- Información del Pago --}}
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-credit-card mr-3 text-green-500"></i> Información del Pago
                </h3>
                <div class="space-y-2 text-gray-800">
                    @if($pedido->pago)
                        <p><strong class="text-gray-600">Método de Pago:</strong> <span class="capitalize font-medium">{{ $pedido->pago->metodo }}</span></p>
                        <p><strong class="text-gray-600">Estado del Pago:</strong> <span class="capitalize font-medium">{{ $pedido->pago->estado }}</span></p>
                        <p><strong class="text-gray-600">Fecha del Pago:</strong> {{ $pedido->pago->fecha->format('d/m/Y H:i') }}</p>
                        @if($pedido->pago->referencia)
                            <p><strong class="text-gray-600">Referencia:</strong>
                                @if(str_contains($pedido->pago->referencia, 'comprobantes/'))
                                    {{-- El enlace ahora tiene una clase para el JS y NO abre en nueva pestaña --}}
                                    <a href="#" data-comprobante-url="{{ Storage::url($pedido->pago->referencia) }}" class="text-blue-600 hover:underline flex items-center open-comprobante-modal">
                                        <i class="fas fa-file-image mr-1"></i> Ver Comprobante
                                    </a>
                                @else
                                    {{-- Si es un ID de transacción (ej. PayPal) --}}
                                    <span class="font-mono text-sm break-all">{{ $pedido->pago->referencia }}</span>
                                @endif
                            </p>
                        @endif
                    @else
                        <p class="text-gray-500 italic">No hay información de pago.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Información de Envío y Repartidor --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
            <i class="fas fa-shipping-fast mr-3 text-indigo-500"></i> Información de Envío
        </h3>
        <div class="space-y-2 text-gray-800">
            @if($pedido->envio)
                <p><strong class="text-gray-600">Estado del Envío:</strong> <span class="capitalize font-medium">{{ $pedido->envio->estado }}</span></p>
                <p><strong class="text-gray-600">Última Actualización Envío:</strong> {{ $pedido->envio->actualizado_en->format('d/m/Y H:i') }}</p>
                <p class="flex items-center">
                    <strong class="text-gray-600 mr-2">Repartidor Asignado:</strong>
                    @if($pedido->envio->repartidor)
                        <span>{{ $pedido->envio->repartidor->nombre }} {{ $pedido->envio->repartidor->apellido }} ({{ $pedido->envio->repartidor->telefono ?? 'N/A' }})</span>
                    @else
                        <span class="text-red-500 font-medium">Pendiente de Asignación</span>
                        {{-- Botón para asignar repartidor, si el estado lo permite --}}
                        @if($pedido->envio->estado == 'asignado')
                            <a href="{{ route('admin.pedidos.assign_form', $pedido->id) }}" class="ml-4 bg-green-600 hover:bg-green-700 text-white text-sm py-1 px-3 rounded-md shadow transition-colors duration-200 flex items-center">
                                <i class="fas fa-truck-loading mr-1"></i> Asignar Ahora
                            </a>
                        @endif
                    @endif
                </p>
            @else
                <p class="text-gray-500 italic">No hay información de envío para este pedido.</p>
            @endif
        </div>
    </div>

    {{-- Productos y Promociones del Pedido --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
            <i class="fas fa-boxes mr-3 text-teal-500"></i> Items del Pedido
        </h3>
        @if ($pedido->items->isEmpty())
            <p class="text-gray-500 italic">Este pedido no contiene items.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Nombre
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Cantidad
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Precio Unitario
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Subtotal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedido->items as $item)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <span class="font-medium text-gray-900">
                                        @if($item->producto_id && !$item->promocion_id) Producto @endif
                                        @if($item->promocion_id) Promoción @endif
                                    </span>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">
                                        @if($item->producto) {{ $item->producto->nombre }} @endif
                                        @if($item->promocion) {{ $item->promocion->nombre }} @endif
                                    </p>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $item->cantidad }}</p>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">S/ {{ number_format($item->precio_unit, 2) }}</p>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">S/ {{ number_format($item->cantidad * $item->precio_unit, 2) }}</p>
                                </td>
                            </tr>
                            {{-- Si es una promoción, listar los productos dentro de la promoción --}}
                            @if($item->promocion && $item->promocion->detalles->isNotEmpty())
                                <tr>
                                    <td colspan="5" class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-xs text-gray-700">
                                        <div class="font-semibold mb-1">Productos de la promoción "{{ $item->promocion->nombre }}":</div>
                                        <ul class="list-disc ml-6 space-y-1">
                                            @foreach($item->promocion->detalles as $detalle)
                                                <li>{{ $detalle->cantidad }} x {{ $detalle->producto->nombre ?? 'Producto Desconocido' }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modal para mostrar el comprobante --}}
    <div id="comprobanteModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-4 max-w-3xl w-full mx-4 relative">
            <button id="closeModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-3xl font-bold">
                &times;
            </button>
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Comprobante de Pago</h3>
            <div class="max-h-[80vh] overflow-auto flex justify-center items-center">
                <img id="modalComprobanteImage" src="" alt="Comprobante de Pago" class="max-w-full h-auto rounded-lg shadow-md">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openModalLinks = document.querySelectorAll('.open-comprobante-modal');
            const comprobanteModal = document.getElementById('comprobanteModal');
            const modalComprobanteImage = document.getElementById('modalComprobanteImage');
            const closeModalButton = document.getElementById('closeModal');

            openModalLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Evita que el navegador siga el enlace
                    const imageUrl = this.dataset.comprobanteUrl;
                    modalComprobanteImage.src = imageUrl;
                    comprobanteModal.classList.remove('hidden'); // Muestra el modal
                });
            });

            closeModalButton.addEventListener('click', function() {
                comprobanteModal.classList.add('hidden'); // Oculta el modal
                modalComprobanteImage.src = ''; // Limpia la imagen al cerrar
            });

            // Opcional: Cerrar modal haciendo clic fuera de él
            comprobanteModal.addEventListener('click', function(e) {
                if (e.target === comprobanteModal) {
                    comprobanteModal.classList.add('hidden');
                    modalComprobanteImage.src = '';
                }
            });
        });
    </script>
@endsection