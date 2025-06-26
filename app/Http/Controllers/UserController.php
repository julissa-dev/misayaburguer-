<?php

namespace App\Http\Controllers;

use App\Models\Usuario; // Asegúrate de que el modelo sea 'Usuario' si ese es el nombre de tu tabla
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Asegúrate de tener Auth importado

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * Muestra una lista de todos los usuarios, incluyendo clientes, administradores y repartidores.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // El administrador puede ver todos los usuarios.
        // Se usa paginate para manejar grandes cantidades de usuarios eficientemente.
        $usuarios = Usuario::paginate(10); // Incluye todos los roles

        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.usuarios.create');
    }

    /**
     * Store a newly created resource in storage.
     * Almacena un nuevo usuario en la base de datos.
     * Solo permite la creación de usuarios con rol 'admin' o 'repartidor'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios', // 'usuarios' es el nombre de tu tabla
            'password' => 'required|string|min:8|confirmed', // 'confirmed' para password_confirmation
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            // VALIDACIÓN CLAVE: Solo permite crear roles 'admin' o 'repartidor'
            'rol' => ['required', Rule::in(['admin', 'repartidor'])],
        ]);

        Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password), // ¡IMPORTANTE! Hashear la contraseña
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'rol' => $request->rol,
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     * Muestra el formulario para editar un usuario específico.
     * El administrador puede editar usuarios de cualquier rol.
     *
     * @param  \App\Models\Usuario  $usuario
     * @return \Illuminate\Http\Response
     */
    public function edit(Usuario $usuario)
    {
        // El administrador puede editar usuarios con cualquier rol, incluyendo 'cliente'.
        return view('admin.usuarios.edit', compact('usuario'));
    }

    /**
     * Update the specified resource in storage.
     * Actualiza la información de un usuario en la base de datos.
     * Permite cambiar el rol del usuario a cualquier tipo si es necesario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Usuario  $usuario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Usuario $usuario)
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('usuarios')->ignore($usuario->id)],
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            // Permite cambiar a cualquier rol (admin, repartidor, cliente) al editar.
            'rol' => ['required', Rule::in(['admin', 'repartidor', 'cliente'])],
        ];

        // Solo valida la contraseña si se proporciona una nueva
        if ($request->filled('password')) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $request->validate($rules);

        $usuario->nombre = $request->nombre;
        $usuario->apellido = $request->apellido;
        $usuario->email = $request->email;
        $usuario->direccion = $request->direccion;
        $usuario->telefono = $request->telefono;
        $usuario->rol = $request->rol;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * Elimina un usuario de la base de datos.
     * Incluye una salvaguarda para evitar que un administrador se elimine a sí mismo.
     *
     * @param  \App\Models\Usuario  $usuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Usuario $usuario)
    {
        // Salvaguarda: Impedir que un administrador se elimine a sí mismo
        // Corregido: Es auth()->user()->id, no auth()->usuario->id()
        if (Auth::check() && $usuario->id === Auth::user()->id && $usuario->rol === 'admin') {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }
        // Puedes añadir más lógica aquí, como evitar eliminar al único administrador del sistema,
        // o si un 'cliente' no debería ser eliminado por el admin (aunque tu petición indica que sí puede).

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
