<table class="min-w-full text-sm text-left">
    <thead class="bg-gray-100 text-gray-600">
        <tr>
            <th class="p-2">Nombre</th>
            <th class="p-2">RUC</th>
            <th class="p-2">Teléfono</th>
            <th class="p-2">Dirección</th>
            <th class="p-2">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($proveedores as $proveedor)
            <tr class="border-t">
                <td class="p-2">{{ $proveedor->nombre }}</td>
                <td class="p-2">{{ $proveedor->ruc ?? '---' }}</td>
                <td class="p-2">{{ $proveedor->telefono ?? '---' }}</td>
                <td class="p-2">{{ $proveedor->direccion ?? '---' }}</td>
                <td class="p-2 flex gap-2">
                    <a href="{{ route('admin.proveedores.edit', $proveedor->id) }}" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">Editar</a>
                    <form action="{{ route('admin.proveedores.destroy', $proveedor->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este proveedor?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-4 text-gray-500">No hay proveedores registrados.</td>
            </tr>
        @endforelse
    </tbody>
</table>
