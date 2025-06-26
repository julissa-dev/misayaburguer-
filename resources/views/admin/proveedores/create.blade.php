@extends('layouts.admin')

@section('titulo', 'Registrar Proveedor')

@section('contenido')
<h2 class="text-2xl font-bold text-gray-800 mb-4">Registrar nuevo proveedor</h2>

@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.proveedores.store') }}" class="bg-white shadow rounded-lg p-6">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">RUC</label>
            <input type="text" name="ruc" value="{{ old('ruc') }}" class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Dirección</label>
            <input type="text" name="direccion" value="{{ old('direccion') }}" class="w-full px-3 py-2 border rounded" />
        </div>
    </div>
    <div class="mt-6 flex justify-between">
        <a href="{{ route('admin.proveedores.index') }}" class="text-sm text-gray-600 hover:underline">← Cancelar</a>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Guardar proveedor
        </button>
    </div>
</form>
@endsection
