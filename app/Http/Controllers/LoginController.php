<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Fachada para la autenticación
use App\Models\Usuario; // Asegúrate de que este sea el modelo correcto de tu usuario
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
            

            // return "¡Bienvenido, {$usuario->nombre} {$usuario->apellido}!";


            // 3. Autenticar al usuario
            Auth::login($usuario);

            // 4. Regenerar la sesión (MUY IMPORTANTE por seguridad)
            $request->session()->regenerate();

            
            // 5. Redirigir al usuario
            return redirect()->intended(route('home'));

        } else {
            // 6. Si el usuario no existe O la contraseña no coincide
            // Mensaje de error más específico
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
