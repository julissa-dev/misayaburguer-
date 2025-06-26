@extends('layouts.admin')

@section('titulo', 'Reporte de Ventas')

@section('contenido')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Reporte de Ventas por Fecha</h2>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <p class="text-gray-700 text-lg">Reporte generado para el rango:
            <span class="font-semibold">{{ $fechaDesde->format('d/m/Y') }}</span> al
            <span class="font-semibold">{{ $fechaHasta->format('d/m/Y') }}</span>
        </p>
        <p class="text-gray-700 text-lg mt-2">Total de Ventas: <span class="font-bold text-green-600">S/ {{ number_format($totalVentasRango, 2) }}</span></p>
        <p class="text-gray-700 text-lg">Cantidad de Pedidos: <span class="font-bold text-blue-600">{{ $cantidadPedidosRango }}</span></p>
    </div>

    {{-- Sección de Productos Más Vendidos --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Top 10 Productos Más Vendidos</h3>
        @if ($productosMasVendidos->isEmpty())
            <p class="text-gray-500">No se encontraron productos vendidos en este rango de fechas.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Producto
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Cantidad Vendida
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Ingreso Total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productosMasVendidos as $item)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $item->producto->nombre ?? 'Producto Desconocido' }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $item->total_cantidad_vendida }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">S/ {{ number_format($item->total_ingreso_producto, 2) }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Sección de Promociones Más Utilizadas --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Top 10 Promociones Más Utilizadas</h3>
        @if ($promocionesMasUtilizadas->isEmpty())
            <p class="text-gray-500">No se encontraron promociones utilizadas en este rango de fechas.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Promoción
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Pedidos con Promoción
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Cantidad de Items
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Ingreso Total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promocionesMasUtilizadas as $item)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $item->promocion->nombre ?? 'Promoción Desconocida' }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $item->total_pedidos_con_promocion }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $item->total_cantidad_promocional }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">S/ {{ number_format($item->total_ingreso_promocional, 2) }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection