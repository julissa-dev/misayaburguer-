<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Importar para manejar archivos

class AdminProductoController extends Controller
{
    /**
     * Muestra una lista paginada de todos los productos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        if ($request->has('search') && $request->search != '') {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        if ($request->has('categoria_id') && $request->categoria_id != '') {
            $query->where('categoria_id', $request->categoria_id);
        }

        $productos = $query->orderBy('nombre', 'asc')->paginate(10);
        $categorias = Categoria::all();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorias = Categoria::all();
        return view('admin.productos.create', compact('categorias'));
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:productos,nombre',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Campo 'imagen' para la subida
            'categoria_id' => 'required|exists:categorias,id',
            'disponible' => 'boolean',
        ]);

        $data = $request->except('imagen'); // Obtener todos los datos excepto el archivo de imagen

        if ($request->hasFile('imagen')) {
            // Guardar la imagen en storage/app/public/img/productos/
            $path = $request->file('imagen')->store('img/productos', 'public');
            $data['imagen_url'] = basename($path); // Guardar solo el nombre del archivo en imagen_url
        } else {
            $data['imagen_url'] = null; // Asegurarse de que sea NULL si no hay imagen
        }

        $producto = Producto::create($data);
        $producto->slug = Str::slug($request->nombre); // Generar slug
        $producto->save();

        return redirect()->route('admin.productos.index')->with('success', 'Producto creado exitosamente!');
    }

    /**
     * Muestra el formulario para editar un producto existente.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        return view('admin.productos.edit', compact('producto', 'categorias'));
    }

    /**
     * Actualiza un producto existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('productos')->ignore($producto->id),
            ],
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Campo 'imagen' para la subida
            'categoria_id' => 'required|exists:categorias,id',
            'disponible' => 'boolean',
        ]);

        $data = $request->except('imagen'); // Obtener todos los datos excepto el archivo de imagen

        if ($request->hasFile('imagen')) {
            // Eliminar la imagen antigua si existe
            if ($producto->imagen_url) {
                Storage::disk('public')->delete('img/productos/' . $producto->imagen_url);
            }
            // Guardar la nueva imagen
            $path = $request->file('imagen')->store('img/productos', 'public');
            $data['imagen_url'] = basename($path); // Guardar solo el nombre del archivo
        } elseif ($request->boolean('remove_imagen')) { // Si se marcó la opción de eliminar imagen
            if ($producto->imagen_url) {
                Storage::disk('public')->delete('img/productos/' . $producto->imagen_url);
            }
            $data['imagen_url'] = null;
        }

        $producto->fill($data); // Usar $data que ya contiene imagen_url correcto
        $producto->slug = Str::slug($request->nombre); // Actualizar slug por si cambia el nombre
        $producto->save();

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado exitosamente!');
    }

    /**
     * Elimina un producto de la base de datos.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Producto $producto)
    {
        try {
            // Eliminar el archivo de imagen asociado si existe
            if ($producto->imagen_url) {
                Storage::disk('public')->delete('img/productos/' . $producto->imagen_url);
            }

            $producto->delete();
            return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->route('admin.productos.index')->with('error', 'No se pudo eliminar el producto. Podría estar relacionado con pedidos o promociones existentes.');
        }
    }
}