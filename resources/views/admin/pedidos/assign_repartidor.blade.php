@extends('layouts.admin')

@section('titulo', 'Asignar Repartidor')

@section('contenido')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Asignar Repartidor al Pedido #{{ $pedido->id }}</h2>
        <a href="{{ route('admin.pedidos.index') }}" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver a Pedidos
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">Por favor corrige los siguientes errores:</span>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.pedidos.assign', $pedido->id) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="repartidor_id" class="block text-sm font-medium text-gray-700">Seleccionar Repartidor:</label>
                <select name="repartidor_id" id="repartidor_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">-- Seleccione un repartidor --</option>
                    @forelse ($repartidores as $repartidor)
                        <option value="{{ $repartidor->id }}" {{ ($pedido->envio && $pedido->envio->repartidor_id == $repartidor->id) ? 'selected' : '' }}>
                            {{ $repartidor->nombre }} {{ $repartidor->apellido }}
                        </option>
                    @empty
                        <option value="" disabled>No hay repartidores disponibles.</option>
                    @endforelse
                </select>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-user-check"></i> Asignar Repartidor
                </button>
            </div>
        </form>
    </div>
@endsection