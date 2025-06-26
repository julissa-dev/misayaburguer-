@extends('layouts.admin')

@section('titulo', 'Editar Insumo')

@section('contenido')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Insumo</h2>

<form method="POST" action="{{ route('inventario.update', $insumo->id) }}" class="bg-white p-6 rounded-lg shadow w-full max-w-md">
    @csrf
    @method('PUT')

    <div class="mb-4">
        <label for="nombre" class="block text-gray-700">Nombre del Insumo</label>
        <input type="text" id="nombre" name="nombre" value="{{ $insumo->nombre }}" required class="w-full mt-1 p-2 border border-gray-300 rounded">
    </div>

    <div class="mb-4">
        <label for="unidad" class="block text-gray-700">Unidad de Medida</label>
        <input type="text" id="unidad" name="unidad" value="{{ $insumo->unidad }}" required class="w-full mt-1 p-2 border border-gray-300 rounded">
    </div>

    <div class="mb-6">
        <label for="cantidad" class="block text-gray-700">Cantidad Disponible</label>
        <input type="number" id="cantidad" name="cantidad" value="{{ $insumo->cantidad }}" min="0" step="0.01" required class="w-full mt-1 p-2 border border-gray-300 rounded">
    </div>

    <div class="flex justify-between">
        <a href="{{ route('inventario.index') }}" class="text-gray-500 hover:underline">← Cancelar</a>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Guardar Cambios</button>
    </div>
</form>
@endsection