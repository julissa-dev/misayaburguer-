<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Importar para manejar archivos

class AdminCategoriaController extends Controller
{
    /**
     * Muestra una lista paginada de todas las categorías.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Categoria::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $categorias = $query->orderBy('nombre', 'asc')->paginate(10);

        return view('admin.categorias.index', compact('categorias'));
    }

    /**
     * Muestra el formulario para crear una nueva categoría.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.categorias.create');
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'imagen_icono' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validar imagen
        ]);

        $data = $request->except('imagen_icono'); // Obtener todos los datos excepto la imagen

        if ($request->hasFile('imagen_icono')) {
            // Guardar la imagen en storage/app/public/img/categorias/
            $path = $request->file('imagen_icono')->store('img/categorias', 'public');
            $data['imagen_icono'] = basename($path); // Guardar solo el nombre del archivo
        } else {
            $data['imagen_icono'] = null; // Asegurarse de que sea NULL si no hay imagen
        }

        Categoria::create($data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría creada exitosamente!');
    }

    /**
     * Muestra el formulario para editar una categoría existente.
     *
     * @param  \App\Models\Categoria  $categoria
     * @return \Illuminate\Http\Response
     */
    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    /**
     * Actualiza una categoría existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Categoria  $categoria
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias')->ignore($categoria->id),
            ],
            'imagen_icono' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validar imagen
        ]);

        $data = $request->except('imagen_icono');

        if ($request->hasFile('imagen_icono')) {
            // Eliminar la imagen antigua si existe
            if ($categoria->imagen_icono) {
                Storage::disk('public')->delete('img/categorias/' . $categoria->imagen_icono);
            }
            // Guardar la nueva imagen
            $path = $request->file('imagen_icono')->store('img/categorias', 'public');
            $data['imagen_icono'] = basename($path);
        } elseif ($request->boolean('remove_imagen_icono')) { // Permite eliminar la imagen existente
            if ($categoria->imagen_icono) {
                Storage::disk('public')->delete('img/categorias/' . $categoria->imagen_icono);
            }
            $data['imagen_icono'] = null;
        }


        $categoria->update($data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada exitosamente!');
    }

    /**
     * Elimina una categoría de la base de datos.
     *
     * @param  \App\Models\Categoria  $categoria
     * @return \Illuminate\Http\Response
     */
    public function destroy(Categoria $categoria)
    {
        try {
            // Eliminar el archivo de imagen asociado si existe
            if ($categoria->imagen_icono) {
                Storage::disk('public')->delete('img/categorias/' . $categoria->imagen_icono);
            }

            $categoria->delete();
            return redirect()->route('admin.categorias.index')->with('success', 'Categoría eliminada exitosamente!');
        } catch (\Exception $e) {
            return redirect()->route('admin.categorias.index')->with('error', 'No se pudo eliminar la categoría. Podría estar relacionada con productos existentes.');
        }
    }
}