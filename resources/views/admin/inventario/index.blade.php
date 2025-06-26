@extends('layouts.admin')

@section('titulo', 'Inventario')

@section('contenido')
<h2 class="text-2xl font-bold text-gray-800 mb-2">Gestión de Inventario</h2>
<p class="mb-4 text-sm text-gray-600">Aquí puedes registrar y editar los insumos necesarios para la preparación de los productos.</p>

<div class="relative w-full max-w-xs mb-4">
    <input type="text" id="search-input" placeholder="Buscar insumo..." 
        class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" 
        autocomplete="off" />
    <button type="button" onclick="clearSearch()" 
        class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-800">
        ✕
    </button>
</div>

<div class="flex justify-end mb-4">
    <a href="{{ route('inventario.create') }}" 
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        + Agregar Insumo
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

{{-- Aquí va el contenedor dinámico de la tabla --}}
<div id="tabla-insumos" class="overflow-x-auto bg-white shadow rounded-lg p-4">
    @include('admin.inventario.partials.tabla-insumos', ['insumos' => $insumos])
</div>

<script>
    const input = document.getElementById('search-input');

    input.addEventListener('input', function () {
        fetchInsumos(this.value);
    });

    function clearSearch() {
        input.value = '';
        fetchInsumos('');
    }

    function fetchInsumos(query) {
        fetch(`{{ route('inventario.index') }}?search=${encodeURIComponent(query) }`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.json()).then(data => {
            document.getElementById('tabla-insumos').innerHTML = data.html;
        });
    }
</script>
@endsection
