<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index()
    {
        return view('autenticacion.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        // 1. Buscar al usuario por el email proporcionado
        $usuario = Usuario::where('email', $request->email)->first();


        if ($usuario && Hash::check($request->password, $usuario->password)) {

            Auth::login($usuario);

            // 4. Regenerar la sesión (MUY IMPORTANTE por seguridad)
            $request->session()->regenerate();

            // 5. Redirigir al usuario
            if ($usuario->rol === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($usuario->rol === 'repartidor') {
                return redirect()->route('repartidor.dashboard'); // Asegúrate de tener esta ruta definida
            } else {
                return redirect()->route('home'); // Ruta para usuarios normales (clientes)
            }
        } else {
            // 6. Si el usuario no existe O la contraseña no coincide
            return back()->withErrors([
                'email' => 'El correo electrónico o la contraseña son incorrectos.',
            ])->onlyInput('email');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Has cerrado sesión correctamente.');
    }
}
