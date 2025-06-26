@extends('layouts.admin')

@section('titulo', 'Gestión de Pedidos')

@section('contenido')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Pedidos</h2>
        {{-- Aquí puedes tener un botón para agregar pedidos manualmente si lo necesitas --}}
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Éxito!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Formulario de Filtro por Estado --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h4 class="text-lg font-semibold text-gray-700 mb-4">Filtrar Pedidos</h4>
        <form action="{{ route('admin.pedidos.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
            <div class="w-full md:w-auto">
                <label for="estado_filtro" class="block text-sm text-gray-600 mb-1">Filtrar por Estado:</label>
                <select name="estado_filtro" id="estado_filtro"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @foreach($estadosDisponibles as $key => $value)
                        <option value="{{ $key }}" {{ ($estadoFiltro == $key) ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200 mt-2 md:mt-0">
                Aplicar Filtro
            </button>
            @if($estadoFiltro)
                <a href="{{ route('admin.pedidos.index') }}" class="w-full md:w-auto bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition-colors duration-200 mt-2 md:mt-0 text-center">
                    Limpiar Filtro
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            ID Pedido
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado Pedido
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado Envío
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Repartidor Asignado
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Fecha Pedido
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr class="
                            @if($pedido->estado == 'cancelado') bg-red-50 @endif
                            @if($pedido->estado == 'entregado') bg-green-50 @endif
                            @if($pedido->envio && $pedido->envio->estado == 'en ruta') bg-blue-50 @endif
                            @if($pedido->envio && $pedido->envio->estado == 'asignado' && !$pedido->envio->repartidor_id) bg-yellow-50 @endif
                        ">
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">#{{ $pedido->id }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $pedido->usuario->nombre ?? 'N/A' }} {{ $pedido->usuario->apellido ?? '' }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"> {{ number_format($pedido->total, 2) }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                {{-- Badge para Estado de Pedido --}}
                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full
                                    @if($pedido->estado == 'en preparacion') text-yellow-900 bg-yellow-200 @endif
                                    @if($pedido->estado == 'en camino') text-blue-900 bg-blue-200 @endif
                                    @if($pedido->estado == 'entregado') text-green-900 bg-green-200 @endif
                                    @if($pedido->estado == 'cancelado') text-red-900 bg-red-200 @endif
                                ">
                                    {{ ucfirst($pedido->estado) }}
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                {{-- Badge para Estado de Envío --}}
                                @if($pedido->envio)
                                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full
                                        @if($pedido->envio->estado == 'asignado' && !$pedido->envio->repartidor_id) text-orange-900 bg-orange-200 @endif
                                        @if($pedido->envio->estado == 'asignado' && $pedido->envio->repartidor_id) text-indigo-900 bg-indigo-200 @endif
                                        @if($pedido->envio->estado == 'en ruta') text-blue-900 bg-blue-200 @endif
                                        @if($pedido->envio->estado == 'entregado') text-green-900 bg-green-200 @endif
                                    ">
                                        {{ ucfirst($pedido->envio->estado) }}
                                        @if($pedido->envio->estado == 'asignado' && !$pedido->envio->repartidor_id) (Pendiente) @endif
                                    </span>
                                @else
                                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight text-gray-500 bg-gray-200 rounded-full">
                                        No Aplica
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    {{ $pedido->envio && $pedido->envio->repartidor ? $pedido->envio->repartidor->nombre . ' ' . $pedido->envio->repartidor->apellido : 'Pendiente' }}
                                </p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $pedido->fecha->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <div class="flex items-center space-x-3">
                                    {{-- Botón para ver detalles del pedido (si tienes una vista show para pedidos) --}}
                                    <a href="{{ route('admin.pedidos.detalles', $pedido->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Botón para asignar repartidor --}}
                                    {{-- Solo muestra el botón si el envío existe Y no hay repartidor asignado --}}
                                    @if ($pedido->envio && !$pedido->envio->repartidor_id)
                                        <a href="{{ route('admin.pedidos.assign_form', $pedido->id) }}" class="text-green-600 hover:text-green-900" title="Asignar Repartidor">
                                            <i class="fas fa-truck-loading"></i>
                                        </a>
                                    @else
                                        {{-- Si ya está asignado o no aplica envío, mostrar un icono de completado/info --}}
                                        <span class="text-gray-400" title="Repartidor Asignado o No Requiere Envío"><i class="fas fa-check-circle"></i></span>
                                    @endif

                                    {{-- Otros botones de acción como editar/cancelar --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                                No hay pedidos registrados con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Paginación --}}
        <div class="mt-4">
            {{ $pedidos->links() }}
        </div>
    </div>
@endsection