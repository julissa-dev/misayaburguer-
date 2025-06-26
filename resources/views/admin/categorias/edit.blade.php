@extends('layouts.admin')

@section('titulo', 'Editar Categoría: ' . $categoria->nombre)

@section('contenido')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar Categoría: {{ $categoria->nombre }}</h2>
        <a href="{{ route('admin.categorias.index') }}" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200 flex items-center gap-2">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Categorías
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.categorias.update', $categoria->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Importante para las actualizaciones --}}

            <div class="mb-6">
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Categoría:</label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $categoria->nombre) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="Ej. Hamburguesas" required>
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="imagen_icono" class="block text-sm font-medium text-gray-700 mb-1">Icono de la Categoría (Imagen):</label>
                @if($categoria->imagen_icono)
                    <div class="flex items-center space-x-4 mb-2">
                        <img src="{{ asset('storage/img/categorias/' . $categoria->imagen_icono) }}" alt="Icono Actual" class="w-16 h-16 object-cover rounded-full border border-gray-300 shadow-sm">
                        <span class="text-sm text-gray-600">{{ $categoria->imagen_icono }}</span>
                        <div class="flex items-center">
                            <input type="checkbox" name="remove_imagen_icono" id="remove_imagen_icono" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                            <label for="remove_imagen_icono" class="ml-2 block text-sm font-medium text-red-700">Eliminar icono actual</label>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mb-2">Sube una nueva imagen para reemplazar la actual.</p>
                @else
                    <p class="text-sm text-gray-500 mb-2">No hay icono cargado actualmente para esta categoría.</p>
                @endif
                <input type="file" name="imagen_icono" id="imagen_icono" accept="image/*"
                       class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('imagen_icono')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-save mr-2"></i> Actualizar Categoría
                </button>
            </div>
        </form>
    </div>
@endsection