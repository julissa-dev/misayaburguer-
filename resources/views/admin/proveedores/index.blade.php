@extends('layouts.admin')

@section('titulo', 'Gestión de proveedores')

@section('contenido')

<h2 class="text-2xl font-bold text-gray-800 mb-2">Gestión de proveedores</h2>
<p class="mb-4 text-sm text-gray-600">Aquí puedes registrar y editar los proveedores que suministran insumos a SAYABURGER</p>
@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<div class="relative w-full max-w-xs mb-4"> <input type="text" id="search-input" placeholder="Buscar proveedor..." class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off" /> <button type="button" onclick="clearSearch()" class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-800"> ✕ </button> </div>
<div class="flex justify-end mb-4"> <a href="{{ route('admin.proveedores.create') }}" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 text-sm"> + Agregar proveedor </a> </div>
<div id="tabla-proveedores" class="overflow-x-auto bg-white shadow rounded-lg p-4"> @include('admin.proveedores.partials.tabla-proveedores', ['proveedores' => $proveedores]) </div>
<script>
    const input = document.getElementById('search-input');
    input.addEventListener('input', function() {
        fetchProveedores(this.value);
    });

    function clearSearch() {
        input.value = '';
        fetchProveedores('');
    }

    function fetchProveedores(query) {
        fetch(`{{ route('admin.proveedores.index') }}?buscar=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.json()).then(data => {
            document.getElementById('tabla-proveedores').innerHTML = data.html;
        });
    }
</script>
@endsection