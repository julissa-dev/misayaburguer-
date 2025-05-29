<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Usuario;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function index()
    {
        return view('autenticacion.register');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'nombre' => 'required|string|max:255|regex:/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/',
                'apellido' => 'required|string|max:255|regex:/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/',
                'email' => 'required|string|email|unique:usuarios,email',
                'password' => 'required|min:8|',
                'telefono' => 'required|string|digits_between:6,9|numeric',

            ],
            [
                'nombre.regex' => 'El campo nombre solo debe contener letras y espacios.'
            ]
        );

        $usuario = new Usuario();
        $usuario->nombre = $request->input('nombre');
        $usuario->apellido = $request->input('apellido');
        $usuario->email = $request->input('email');
        $usuario->password = bcrypt($request->input('password'));
        $usuario->telefono = $request->input('telefono');
        $usuario->save();

        $carrito = new Carrito();
        $carrito->usuario_id = $usuario->id;
        // $carrito->creado_en = now(); // ¡ELIMINA ESTA LÍNEA!
        $carrito->save();



        return redirect()->route('home');
    }
}
