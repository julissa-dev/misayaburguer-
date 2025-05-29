<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// ¡IMPORTANTE! Extender de la clase User de Laravel
use Illuminate\Foundation\Auth\User as Authenticatable; // Renombramos User a Authenticatable para evitar conflicto con tu modelo Usuario
use Illuminate\Notifications\Notifiable; // Generalmente se incluye para notificaciones, es opcional si no las usas

class Usuario extends Authenticatable // ¡Ahora extiende de Authenticatable!
{
    use HasFactory, Notifiable; // Notifiable es opcional

    protected $table = 'usuarios';

    /**
     * Los atributos que son asignables masivamente.
     * Esto es importante si usas Usuario::create([...])
     * Si no los defines, Laravel protegerá contra asignación masiva.
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'telefono',
        // Añade aquí cualquier otro campo que puedas asignar masivamente al crear un usuario
    ];

    /**
     * Los atributos que deben ser ocultados para la serialización.
     * Útil para no exponer contraseñas al convertir el modelo a array/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token', // Si usas "recordarme" en el login
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Si usas verificación de email
        'password' => 'hashed', // A partir de Laravel 10+, esto es la forma recomendada para hashear la contraseña automáticamente al guardarla.
                               // Si usas Hash::make() explícitamente en el controlador, puedes quitarlo.
    ];


    // Definición del accesador y mutador para el atributo 'nombre'
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtolower($value), // Forma corta de Closure
            get: fn ($value) => ucwords($value),   // Forma corta de Closure
        );
    }

    // Opcional: Si quieres un accesador/mutador similar para el apellido
    protected function apellido(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtolower($value),
            get: fn ($value) => ucwords($value),
        );
    }
}