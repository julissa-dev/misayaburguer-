@extends('layouts.repartidor')

@section('titulo', 'Detalles del Envío #' . $envio->id)

@section('contenido')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-4 sm:mb-0">
            Envío: <span class="font-semibold text-blue-700">#{{ $envio->id }}</span>
        </h2>
        <a href="{{ route('repartidor.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-lg shadow-md transition-all duration-200 ease-in-out transform hover:scale-105">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Pedidos Asignados
        </a>
    </div>

    {{-- Mensajes de Notificación --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm" role="alert">
            <strong class="font-bold">¡Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm" role="alert">
            <strong class="font-bold">¡Validación Fallida!</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-5 border-b-2 border-gray-200 pb-3">
            <i class="fas fa-info-circle text-blue-500 mr-3"></i> Información del Pedido
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700 text-base">
            <div class="space-y-3">
                <p><strong class="font-semibold text-gray-800">ID Pedido:</strong> <span class="text-blue-600 font-medium">#{{ $envio->pedido->id }}</span></p>
                <p><strong class="font-semibold text-gray-800">Cliente:</strong> {{ $envio->pedido->usuario->nombre ?? 'N/A' }} {{ $envio->pedido->usuario->apellido ?? '' }}</p>
                <p><strong class="font-semibold text-gray-800">Teléfono Cliente:</strong> {{ $envio->pedido->usuario->telefono ?? 'N/A' }}</p>
                <p><strong class="font-semibold text-gray-800">Dirección de Envío:</strong> {{ $envio->pedido->direccion }}</p>
                <p><strong class="font-semibold text-gray-800">Fecha de Pedido:</strong> {{ $envio->pedido->fecha->format('d/m/Y H:i') }}</p>
            </div>
            <div class="space-y-3">
                <p><strong class="font-semibold text-gray-800">Total del Pedido:</strong> <span class="text-green-600 font-bold text-lg">S/ {{ number_format($envio->pedido->total, 2) }}</span></p>
                <p><strong class="font-semibold text-gray-800">Estado del Pedido:</strong>
                    <span class="relative inline-block px-4 py-1.5 font-bold leading-tight rounded-full text-sm shadow-sm
                        @if($envio->pedido->estado == 'en preparacion') text-yellow-900 bg-yellow-200 @endif
                        @if($envio->pedido->estado == 'en camino') text-blue-900 bg-blue-200 @endif
                        @if($envio->pedido->estado == 'entregado') text-green-900 bg-green-200 @endif
                        @if($envio->pedido->estado == 'cancelado') text-red-900 bg-red-200 @endif
                    ">
                        {{ ucfirst($envio->pedido->estado) }}
                    </span>
                </p>
                <p><strong class="font-semibold text-gray-800">Estado del Envío:</strong>
                    <span class="relative inline-block px-4 py-1.5 font-bold leading-tight rounded-full text-sm shadow-sm
                        @if($envio->estado == 'asignado' && !$envio->repartidor_id) text-orange-900 bg-orange-200 @endif
                        @if($envio->estado == 'asignado' && $envio->repartidor_id) text-indigo-900 bg-indigo-200 @endif
                        @if($envio->estado == 'en ruta') text-blue-900 bg-blue-200 @endif
                        @if($envio->estado == 'entregado') text-green-900 bg-green-200 @endif
                        
                    ">
                        {{ ucfirst($envio->estado) }}
                        @if($envio->estado == 'asignado' && !$envio->repartidor_id) (Pendiente Asignación) @endif
                    </span>
                </p>
                <p><strong class="font-semibold text-gray-800">Asignado a:</strong> {{ $envio->repartidor->nombre ?? 'N/A' }} {{ $envio->repartidor->apellido ?? '' }}</p>
                <p><strong class="font-semibold text-gray-800">Fecha de Asignación:</strong> {{ $envio->actualizado_en ? $envio->actualizado_en->format('d/m/Y H:i') : 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Sección de Acciones Rápidas de Contacto --}}
    @if($envio->pedido->usuario->telefono)
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-5 border-b-2 border-gray-200 pb-3">
            <i class="fab fa-whatsapp text-green-500 mr-3"></i> Acciones Rápidas de Contacto
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $customerPhone = preg_replace('/[^0-9]/', '', $envio->pedido->usuario->telefono); // Limpiar el número de teléfono
                // Considera añadir el código de país si no está incluido en el teléfono de la DB, ej. "+51" para Perú
                // $customerPhone = '51' . $customerPhone; // Si sabes que todos son de Perú y no tienen el +51
            @endphp

            @if($customerPhone)
                {{-- Botón para mensaje "Estoy llegando" --}}
                <a href="https://wa.me/{{ $customerPhone }}?text={{ urlencode('¡Hola! Soy tu repartidor/a y estoy llegando con tu pedido.') }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fab fa-whatsapp mr-2"></i> Estoy llegando
                </a>

                {{-- Botón para mensaje "No encuentro la dirección" --}}
                <a href="https://wa.me/{{ $customerPhone }}?text={{ urlencode('¡Hola! Soy tu repartidor/a del pedido. Tengo dificultades para encontrar la dirección, ¿podrías darme alguna indicación adicional?') }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fab fa-whatsapp mr-2"></i> No encuentro dirección
                </a>

                {{-- Botón para abrir chat general --}}
                <a href="https://wa.me/{{ $customerPhone }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 ease-in-out transform hover:scale-105">
                    <i class="fab fa-whatsapp mr-2"></i> Abrir Chat
                </a>
            @else
                <p class="col-span-full text-gray-600 text-center">No se encontró un número de teléfono válido para el cliente.</p>
            @endif
        </div>
    </div>
    @endif


    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-5 border-b-2 border-gray-200 pb-3">
            <i class="fas fa-clipboard-list text-purple-500 mr-3"></i> Items del Pedido
        </h3>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full leading-normal divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Producto/Promoción
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Cantidad
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Precio Unitario
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Subtotal
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($envio->pedido->items as $item)
                        <tr>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    @if($item->producto)
                                        {{ $item->producto->nombre }}
                                    @elseif($item->promocion)
                                        Promoción: {{ $item->promocion->nombre }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->cantidad }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                S/ {{ number_format($item->precio_unit, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                S/ {{ number_format($item->cantidad * $item->precio_unit, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-5 text-sm text-center text-gray-500">
                                Este pedido no contiene ítems.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Formulario para actualizar estado del envío --}}
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-5 border-b-2 border-gray-200 pb-3">
            <i class="fas fa-truck text-orange-500 mr-3"></i> Actualizar Estado del Envío
        </h3>
        @if (!in_array($envio->estado, ['entregado', 'cancelado', 'fallido'])) {{-- Si el envío no está ya entregado, cancelado o fallido --}}
            <form action="{{ route('repartidor.envios.update_status', $envio->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="flex flex-col md:flex-row gap-5 items-end">
                    <div class="w-full md:w-1/2">
                        <label for="estado_envio" class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Nuevo Estado:</label>
                        <select name="estado" id="estado_envio" required
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base">
                            <option value="en ruta" {{ $envio->estado == 'en ruta' ? 'selected' : '' }}>En Ruta</option>
                            <option value="entregado" {{ $envio->estado == 'entregado' ? 'selected' : '' }}>Entregado</option>
                            
                        </select>
                        @error('estado')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg transition-all duration-200 ease-in-out transform hover:scale-105 flex items-center justify-center gap-2">
                            <i class="fas fa-sync-alt"></i> Actualizar Envío
                        </button>
                    </div>
                </div>
            </form>
        @else
            <p class="mt-4 text-lg text-gray-700 font-medium">
                <i class="fas fa-check-circle text-green-500 mr-2"></i> Este envío ya ha sido <span class="font-bold">{{ ucfirst($envio->estado) }}</span>. No se pueden realizar más actualizaciones de estado.
            </p>
        @endif
    </div>
@endsection
