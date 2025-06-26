@extends('layouts.admin')

@section('titulo', 'Crear Nueva Promoción')

@section('contenido')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Crear Nueva Promoción</h2>
        <a href="{{ route('admin.promociones.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200 flex items-center gap-2">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Promociones
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">¡Error!</strong>
            <ul class="mt-3 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-8">
        <form action="{{ route('admin.promociones.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-8">
                {{-- Nombre de la Promoción --}}
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">Nombre de la Promoción:</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                           placeholder="Ej. Combo Familiar Supremo" required>
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Precio Promocional --}}
                <div>
                    <label for="precio_promocional" class="block text-sm font-semibold text-gray-700 mb-1">Precio Promocional (S/.):</label>
                    <input type="number" name="precio_promocional" id="precio_promocional" value="{{ old('precio_promocional') }}" step="0.01" min="0"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                           placeholder="Ej. 35.00" required>
                    @error('precio_promocional')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Imagen de la Promoción --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="imagen" class="block text-sm font-semibold text-gray-700 mb-1">Imagen de la Promoción:</label>
                    <input type="file" name="imagen" id="imagen" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('imagen')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Sube una imagen representativa para tu promoción (JPG, PNG, GIF, SVG, máximo 2MB).</p>
                </div>
            </div>

            {{-- Descripción --}}
            <div class="mb-8">
                <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-1">Descripción:</label>
                <textarea name="descripcion" id="descripcion" rows="5"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                          placeholder="Detalles de la promoción, qué incluye, beneficios, etc.">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Promoción Activa --}}
            <div class="mb-8 flex items-center">
                <input type="checkbox" name="activa" id="activa" value="1" {{ old('activa', 1) ? 'checked' : '' }}
                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                <label for="activa" class="ml-2 block text-base font-medium text-gray-900">Activa (La promoción se mostrará a los clientes)</label>
                @error('activa')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botón de Enviar --}}
            <div class="flex justify-end mt-8">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md shadow-lg transition-all duration-200 flex items-center gap-2 transform hover:scale-105">
                    <i class="fas fa-save mr-2 text-lg"></i> Crear Promoción
                </button>
            </div>
        </form>
    </div>
@endsection