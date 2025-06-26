@extends('layouts.admin')

@section('titulo', 'Editar Promoción')

@section('contenido')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar Promoción: {{ $promocion->nombre }}</h2>
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
        {{-- CORREGIDO: Pasar el ID de la promoción directamente --}}
        <form action="{{ route('admin.promociones.update', $promocion->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Importante para las actualizaciones --}}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-8">
                {{-- Nombre de la Promoción --}}
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">Nombre de la Promoción:</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $promocion->nombre) }}"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                           placeholder="Ej. Combo Familiar Supremo" required>
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Precio Promocional --}}
                <div>
                    <label for="precio_promocional" class="block text-sm font-semibold text-gray-700 mb-1">Precio Promocional (S/.):</label>
                    <input type="number" name="precio_promocional" id="precio_promocional" value="{{ old('precio_promocional', $promocion->precio_promocional) }}" step="0.01" min="0"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                           placeholder="Ej. 35.00" required>
                    @error('precio_promocional')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Imagen de la Promoción --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="imagen" class="block text-sm font-semibold text-gray-700 mb-1">Imagen de la Promoción:</label>
                    @if ($promocion->imagen_url)
                        <div class="mb-3">
                            <p class="text-sm text-gray-600">Imagen actual:</p>
                            <img src="{{ asset('storage/img/promociones/' . $promocion->imagen_url) }}" alt="{{ $promocion->nombre }}" class="h-24 w-24 object-cover rounded-lg shadow mt-2">
                            <div class="mt-2 flex items-center">
                                <input type="checkbox" name="remove_imagen" id="remove_imagen" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label for="remove_imagen" class="ml-2 text-sm text-gray-700">Eliminar imagen actual</label>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="imagen" id="imagen" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('imagen')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Sube una nueva imagen o déjalo en blanco para mantener la actual.</p>
                </div>
            </div>

            {{-- Descripción --}}
            <div class="mb-8">
                <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-1">Descripción:</label>
                <textarea name="descripcion" id="descripcion" rows="5"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                          placeholder="Detalles de la promoción, qué incluye, beneficios, etc.">{{ old('descripcion', $promocion->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sección de Productos para Añadir con Cantidad (Se mantiene en EDITAR) --}}
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Productos Incluidos en la Promoción</h3>

                {{-- Filtro por Categoría --}}
                <div class="mb-4">
                    <label for="categoria_filtro" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Categoría:</label>
                    <select id="categoria_filtro"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="all">Todas las Categorías</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Selector y botón para añadir producto --}}
                <div class="mb-4 flex items-end gap-3">
                    <div class="flex-grow">
                        <label for="producto_selector" class="block text-sm font-medium text-gray-700 mb-1">Producto:</label>
                        <select id="producto_selector"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">-- Seleccionar Producto --</option>
                            {{-- Las opciones de productos se cargarán aquí dinámicamente con JS --}}
                        </select>
                    </div>
                    <button type="button" id="add_product_btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow transition-colors duration-200">
                        <i class="fas fa-plus-circle mr-2"></i> Añadir
                    </button>
                </div>

                {{-- Contenedor para productos añadidos dinámicamente --}}
                <div class="mt-6">
                    <h4 class="text-lg font-medium text-gray-700 mb-3">Productos Asociados:</h4>
                    <div id="productos_asociados_container" class="space-y-4">
                        {{-- Mensaje inicial si no hay productos --}}
                        <p id="no_products_message" class="text-gray-500 text-sm hidden">No hay productos asociados a esta promoción.</p>

                        {{-- Productos ya asociados a la promoción o recuperados de old() data --}}
                        @php
                            $associatedProducts = old('productos_promocion', $promocion->productos->map(function($p) {
                                return ['id' => $p->id, 'cantidad' => $p->pivot->cantidad];
                            })->toArray());
                        @endphp

                        @foreach($associatedProducts as $index => $associatedProduct)
                            @php
                                $productData = $productos->firstWhere('id', $associatedProduct['id']);
                            @endphp
                            @if($productData)
                                <div class="flex items-center space-x-3 border border-gray-200 p-3 rounded-md bg-gray-50 product-row" data-product-id="{{ $productData->id }}">
                                    <div class="flex-grow">
                                        <p class="font-medium text-gray-800">{{ $productData->nombre }}</p>
                                        <p class="text-sm text-gray-600">Precio Unitario: S/ {{ number_format($productData->precio, 2) }}</p>
                                    </div>
                                    <input type="number"
                                           name="productos_promocion[{{ $index }}][cantidad]"
                                           value="{{ old('productos_promocion.' . $index . '.cantidad', $associatedProduct['cantidad'] ?? 1) }}"
                                           min="1"
                                           placeholder="Cantidad"
                                           class="w-24 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                    <input type="hidden" name="productos_promocion[{{ $index }}][id]" value="{{ $productData->id }}">
                                    <button type="button" class="remove-product-btn text-red-600 hover:text-red-800 p-2 rounded-full">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @error('productos_promocion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @error('productos_promocion.*.id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @error('productos_promocion.*.cantidad')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Promoción Activa --}}
            <div class="mb-8 flex items-center">
                <input type="hidden" name="activa" value="0">
                <input type="checkbox" name="activa" id="activa" value="1" {{ old('activa', $promocion->activa) ? 'checked' : '' }}
                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                <label for="activa" class="ml-2 block text-base font-medium text-gray-900">Activa (La promoción se mostrará a los clientes)</label>
                @error('activa')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botón de Enviar --}}
            <div class="flex justify-end mt-8">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md shadow-lg transition-all duration-200 flex items-center gap-2 transform hover:scale-105">
                    <i class="fas fa-save mr-2 text-lg"></i> Actualizar Promoción
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoriaFiltro = document.getElementById('categoria_filtro');
            const productoSelector = document.getElementById('producto_selector');
            const addProductBtn = document.getElementById('add_product_btn');
            const productosAsociadosContainer = document.getElementById('productos_asociados_container');
            const noProductsMessage = document.getElementById('no_products_message');

            // Almacenamos los datos de todos los productos para filtrar dinámicamente.
            const allProductsData = [];
            @foreach($productos as $producto)
                allProductsData.push({
                    id: '{{ $producto->id }}',
                    nombre: '{{ $producto->nombre }}',
                    precio: '{{ number_format($producto->precio, 2) }}',
                    categoria_id: '{{ $producto->categoria_id ?? '' }}'
                });
            @endforeach

            // Inicializa el contador de productos basado en los que ya están en el DOM (ya sean de la promo o de old data)
            let productCounter = productosAsociadosContainer.querySelectorAll('.product-row').length;

            // Función para añadir una fila de producto dinámicamente
            function addProductRow(productId, productName, quantity = 1) {
                // Previene añadir el mismo producto múltiples veces
                if (document.querySelector(`.product-row[data-product-id="${productId}"]`)) {
                    alert('Este producto ya ha sido añadido a la promoción.');
                    return;
                }

                if (noProductsMessage) {
                    noProductsMessage.classList.add('hidden');
                }

                const productRow = document.createElement('div');
                productRow.classList.add('flex', 'items-center', 'space-x-3', 'border', 'border-gray-200', 'p-3', 'rounded-md', 'bg-gray-50', 'product-row');
                productRow.dataset.productId = productId;

                const productInfo = allProductsData.find(p => p.id == productId);
                const displayPrice = productInfo ? `(S/ ${productInfo.precio})` : '';

                productRow.innerHTML = `
                    <div class="flex-grow">
                        <p class="font-medium text-gray-800">${productName} ${displayPrice}</p>
                    </div>
                    <input type="number"
                           name="productos_promocion[${productCounter}][cantidad]"
                           value="${quantity}"
                           min="1"
                           placeholder="Cantidad"
                           class="w-24 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <input type="hidden" name="productos_promocion[${productCounter}][id]" value="${productId}">
                    <button type="button" class="remove-product-btn text-red-600 hover:text-red-800 p-2 rounded-full">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                productosAsociadosContainer.appendChild(productRow);
                productCounter++;
            }

            // Función para aplicar el filtro de categoría al selector de productos
            function applyCategoryFilter() {
                const selectedCategoryId = categoriaFiltro.value;

                productoSelector.innerHTML = '<option value="">-- Seleccionar Producto --</option>';

                allProductsData.forEach(product => {
                    const productCategoryId = product.categoria_id;

                    if (selectedCategoryId === 'all' || (productCategoryId !== '' && productCategoryId == selectedCategoryId)) {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.dataset.nombre = product.nombre;
                        option.dataset.precio = product.precio;
                        option.dataset.categoriaId = product.categoria_id;
                        option.textContent = `${product.nombre} (S/ ${product.precio})`;
                        productoSelector.appendChild(option);
                    }
                });
                productoSelector.value = '';
            }

            // Listener para el botón "Añadir Producto"
            addProductBtn.addEventListener('click', function() {
                const selectedOption = productoSelector.options[productoSelector.selectedIndex];
                const productId = selectedOption.value;
                const productName = selectedOption.dataset.nombre;

                if (productId) {
                    addProductRow(productId, productName);
                } else {
                    alert('Por favor, selecciona un producto para añadir.');
                }
            });

            // Listener para eliminar producto (delegación de eventos)
            productosAsociadosContainer.addEventListener('click', function(event) {
                if (event.target.closest('.remove-product-btn')) {
                    const rowToRemove = event.target.closest('.product-row');
                    if (rowToRemove) {
                        rowToRemove.remove();
                        if (productosAsociadosContainer.querySelectorAll('.product-row').length === 0 && noProductsMessage) {
                            noProductsMessage.classList.remove('hidden');
                        }
                    }
                }
            });

            // --- Lógica de inicialización al cargar la página ---

            // 1. Aplicar el filtro de categoría inicialmente
            applyCategoryFilter();

            // 2. Controlar la visibilidad del mensaje "No hay productos asociados"
            if (productosAsociadosContainer.querySelectorAll('.product-row').length === 0) {
                noProductsMessage.classList.remove('hidden');
            } else {
                noProductsMessage.classList.add('hidden');
            }

            // 3. Añadir el listener para el cambio en el filtro de categoría
            categoriaFiltro.addEventListener('change', applyCategoryFilter);
        });
    </script>
    
@endsection