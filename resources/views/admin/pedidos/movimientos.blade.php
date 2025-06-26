@extends('layouts.admin')

@section('titulo', 'Movimientos de Insumos')

@section('contenido')
<h2 class="text-2xl font-bold text-gray-800 mb-2">Movimientos de Insumos del Pedido #{{ $pedido->id }}</h2>
<h2 class="text-2xl font-bold text-gray-800 mb-2">Historial de Movimientos de Insumos</h2>
<p class="text-gray-600 mb-6">Aquí se registran los insumos utilizados en cada venta.</p>

<a href="{{ route('admin.pedidos.index') }}" class="inline-block mb-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
    ← Volver a Pedidos
</a>
<div class="bg-white shadow rounded-lg overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-100 text-gray-600">
            <tr>
                <th class="text-left p-4">Insumo</th>
                <th class="text-left p-4">Cantidad Utilizada</th>
                <th class="text-left p-4">Pedido</th>
                <th class="text-left p-4">Cliente</th>
                <th class="text-left p-4">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movimientos as $mov)
            <tr class="border-t">
                <td class="p-4">{{ $mov->insumo->nombre }}</td>
                <td class="p-4">{{ $mov->cantidad_utilizada }} {{ $mov->insumo->unidad }}</td>
                <td class="p-4">#{{ $mov->pedido->id }}</td>
                <td class="p-4">{{ $mov->pedido->usuario->nombre ?? 'N/A' }}</td>
                <td class="p-4">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-gray-500">No hay movimientos registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection