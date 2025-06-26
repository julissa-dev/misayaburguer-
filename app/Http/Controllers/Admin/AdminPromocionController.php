<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Promocion;
use App\Models\Producto; // Para listar los productos disponibles
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Para manejar archivos
use Illuminate\Support\Str; // Importar la clase Str

class AdminPromocionController extends Controller
{
    /**
     * Muestra una lista paginada de todas las promociones.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Promocion::query();

        // Puedes añadir un filtro de búsqueda si lo deseas
        if ($request->has('search') && $request->search != '') {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $promociones = $query->orderBy('nombre', 'asc')->paginate(10);

        return view('admin.promociones.index', compact('promociones'));
    }

    /**
     * Muestra el formulario para crear una nueva promoción.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // En este flujo, 'create' solo necesita los datos de la promoción,
        // no necesita 'productos' ni 'categorias' aquí, ya que los productos se añaden después.
        // Los dejo comentados por si en algún futuro los necesitas en esta vista por otro motivo.
        // $productos = Producto::orderBy('nombre')->get();
        // $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.promociones.create'); // Ya no pasamos productos/categorias
    }

    /**
     * Almacena una nueva promoción en la base de datos (solo la promoción, sin productos).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Valida solo los datos de la promoción
        $request->validate([
            'nombre' => 'required|string|max:255|unique:promociones,nombre',
            'descripcion' => 'nullable|string',
            'precio_promocional' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Campo 'imagen' para la subida
            'activa' => 'boolean',
        ], [
            // Mensajes de error específicos para la promoción
        ]);

        // Excluir el archivo de imagen de la asignación masiva inicial
        $data = $request->except(['imagen']);

        // Maneja la subida de la imagen
        if ($request->hasFile('imagen')) {
            $imageName = Str::uuid() . '.' . $request->file('imagen')->getClientOriginalExtension();
            // CORREGIDO: Especifica 'public' como el disco
            $request->file('imagen')->storeAs('img/promociones', $imageName, 'public');
            $data['imagen_url'] = $imageName;
        } else {
            $data['imagen_url'] = null;
        }

        // Crea la nueva promoción
        $promocion = Promocion::create($data);

        // Redirige con un mensaje de éxito
        return redirect()->route('admin.promociones.index')->with('success', 'Promoción creada exitosamente. ¡Ahora puedes añadir productos!');
    }

    /**
     * Muestra el formulario para añadir productos a una promoción existente.
     *
     * @param  \App\Models\Promocion  $promocion
     * @return \Illuminate\Http\Response
     */
    public function addProductForm(Promocion $promocion)
    {
        $productos = Producto::orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.promociones.add-products', compact('promocion', 'productos', 'categorias'));
    }

    /**
     * Guarda los productos asociados a una promoción existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promocion  $promocion
     * @return \Illuminate\Http\Response
     */
    public function saveProducts(Request $request, Promocion $promocion)
    {
        $request->validate([
            'productos_promocion' => 'nullable|array', // Puede no haber productos
            'productos_promocion.*.id' => 'required_with:productos_promocion|exists:productos,id',
            'productos_promocion.*.cantidad' => 'required_with:productos_promocion|integer|min:1',
        ], [
            'productos_promocion.*.id.required_with' => 'El ID de un producto de la promoción es obligatorio.',
            'productos_promocion.*.id.exists' => 'Uno o más productos seleccionados no son válidos.',
            'productos_promocion.*.cantidad.required_with' => 'La cantidad para cada producto de la promoción es obligatoria.',
            'productos_promocion.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos_promocion.*.cantidad.min' => 'La cantidad de un producto debe ser al menos 1.',
        ]);

        $productosSync = [];
        if ($request->has('productos_promocion')) {
            foreach ($request->productos_promocion as $producto) {
                $productosSync[$producto['id']] = ['cantidad' => $producto['cantidad']];
            }
        }
        // El método sync() añadirá, actualizará o eliminará asociaciones existentes.
        $promocion->productos()->sync($productosSync);

        return redirect()->route('admin.promociones.index')->with('success', 'Productos de la promoción actualizados exitosamente.');
    }

    /**
     * Muestra el formulario para editar una promoción existente.
     * Aquí se editarán los datos de la promoción Y sus productos asociados.
     *
     * @param  \App\Models\Promocion  $promocion
     * @return \Illuminate\Http\Response
     */
    public function edit(Promocion $promocion)
    {
        $productos = Producto::orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.promociones.edit', compact('promocion', 'productos', 'categorias'));
    }

    /**
     * Actualiza una promoción existente en la base de datos (incluye productos).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promocion  $promocion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promocion $promocion)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('promociones')->ignore($promocion->id),
            ],
            'descripcion' => 'nullable|string',
            'precio_promocional' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'activa' => 'boolean',
            'productos_promocion' => 'nullable|array',
            'productos_promocion.*.id' => 'required_with:productos_promocion|exists:productos,id',
            'productos_promocion.*.cantidad' => 'required_with:productos_promocion|integer|min:1',
        ], [
            'productos_promocion.*.id.required_with' => 'El ID de un producto de la promoción es obligatorio.',
            'productos_promocion.*.id.exists' => 'Uno o más productos seleccionados no son válidos.',
            'productos_promocion.*.cantidad.required_with' => 'La cantidad para cada producto de la promoción es obligatoria.',
            'productos_promocion.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos_promocion.*.cantidad.min' => 'La cantidad de un producto debe ser al menos 1.',
        ]);

        $data = $request->except(['imagen', 'productos_promocion', 'remove_imagen']);

        if ($request->hasFile('imagen')) {
            if ($promocion->imagen_url) {
                Storage::disk('public')->delete('img/promociones/' . $promocion->imagen_url);
            }
            $imageName = Str::uuid() . '.' . $request->file('imagen')->getClientOriginalExtension();
            $request->file('imagen')->storeAs('public/img/promociones', $imageName);
            $data['imagen_url'] = $imageName;
        } elseif ($request->boolean('remove_imagen')) {
            if ($promocion->imagen_url) {
                Storage::disk('public')->delete('img/promociones/' . $promocion->imagen_url);
            }
            $data['imagen_url'] = null;
        }

        $promocion->update($data);

        $productosSync = [];
        if ($request->has('productos_promocion')) {
            foreach ($request->productos_promocion as $producto) {
                $productosSync[$producto['id']] = ['cantidad' => $producto['cantidad']];
            }
        }
        $promocion->productos()->sync($productosSync);

        return redirect()->route('admin.promociones.index')->with('success', 'Promoción actualizada exitosamente!');
    }

    /**
     * Elimina una promoción de la base de datos.
     *
     * @param  \App\Models\Promocion  $promocion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promocion $promocion)
    {
        try {
            $promocion->productos()->detach(); // Elimina las entradas en promocion_detalle
            if ($promocion->imagen_url) {
                Storage::disk('public')->delete('img/promociones/' . $promocion->imagen_url);
            }
            $promocion->delete();
            return redirect()->route('admin.promociones.index')->with('success', 'Promoción eliminada exitosamente!');
        } catch (\Exception $e) {
            return redirect()->route('admin.promociones.index')->with('error', 'No se pudo eliminar la promoción. Asegúrese de que no haya dependencias externas: ' . $e->getMessage());
        }
    }
}
