@extends('layouts.admin')

@section('titulo', 'Agregar Insumo')

@section('contenido')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Agregar Insumo</h2>

<form method="POST" action="{{ route('inventario.store') }}" class="bg-white p-6 rounded-lg shadow w-full max-w-md">
    @csrf

    <div class="mb-4">
        <label for="nombre" class="block text-gray-700">Nombre del Insumo</label>
        <input type="text" id="nombre" name="nombre" required class="w-full mt-1 p-2 border border-gray-300 rounded">
    </div>

    <div class="mb-4">
        <label for="unidad" class="block text-gray-700">Unidad de Medida</label>
        <input type="text" id="unidad" name="unidad" placeholder="Ej. kg, litros, unidades" required class="w-full mt-1 p-2 border border-gray-300 rounded">
    </div>

    <div class="mb-6">
        <label for="cantidad" class="block text-gray-700">Cantidad Inicial</label>
        <input type="number" id="cantidad" name="cantidad" min="0" step="0.01" required class="w-full mt-1 p-2 border border-gray-300 rounded">
    </div>

    <div class="flex justify-between">
        <a href="{{ route('inventario.index') }}" class="text-gray-500 hover:underline">← Cancelar</a>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Guardar Insumo</button>
    </div>
</form>
@endsection