<table class="min-w-full text-sm">
    <thead class="bg-gray-100 text-gray-600">
        <tr>
            <th class="text-left p-4">Nombre</th>
            <th class="text-left p-4">Unidad</th>
            <th class="text-left p-4">Cantidad</th>
            <th class="text-left p-4">Acciones</th>
        </tr>
    </thead>
    <tbody> @forelse ($insumos as $insumo) <tr class="border-t">
            <td class="p-4">{{ $insumo->nombre }}</td>
            <td class="p-4">{{ $insumo->unidad }}</td>
            <td class="p-4">{{ $insumo->cantidad }}</td>
            <td class="p-4">
                <div class="flex gap-2"> <a href="{{ route('inventario.edit', $insumo) }}" class="text-blue-600 hover:underline">Editar</a>

                    <form action="{{ route('inventario.destroy', $insumo->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este insumo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="p-4 text-center text-gray-500">No se encontraron resultados.</td>
        </tr>
        @endforelse
    </tbody>
</table>