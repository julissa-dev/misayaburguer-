<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="stylesheet" href="{{ asset('css/autenticacion/register.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
</head>

<body>

    <div class="auth-container">

        <a href="{{ route('home') }}">

            <button class="cerrar-formulario" aria-label="Volver a la página anterior">
                <span aria-hidden="true">&times;</span>
            </button>
        </a>

        <div id="logo-container">
            <img src="{{ asset('img/front/log.png') }}" alt="Tu Logo">
        </div>

        <div id="registro-form-container">
            <h1 class="form-header">Registrarse</h1>
            <form class="register-form" id="registro-form" action="{{ route('register.store2') }}" method="POST">

                @csrf

                <div class="form-group">
                    <label for="registro-nombre">Nombre:</label>
                    <input type="text" id="registro-nombre" name="nombre" value="{{ old('nombre') }}"
                        class="@error('nombre') is-invalid @enderror">
                    @error('nombre')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="registro-apellido">Apellido:</label>
                    <input type="text" id="registro-apellido" name="apellido" value="{{ old('apellido') }}"
                        class="@error('apellido') is-invalid @enderror">
                    @error('apellido')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="registro-correo">Correo:</label>
                    <input type="text" id="registro-email" name="email" value="{{ old('email') }}"
                        class="@error('email') is-invalid @enderror">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="registro-password">Contraseña:</label>
                    <input type="password" id="registro-password" name="password" value="{{ old('password') }}"
                        class="@error('password') is-invalid @enderror">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="registro-password">rol:</label>
                    <input type="rol" id="registro-password" name="rol" value="{{ old('rol') }}"
                        class="@error('rol') is-invalid @enderror">
                    @error('rol')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="registro-direccion">Teléfono:</label>
                    <input type="text" id="registro-telefono" name="telefono" value="{{ old('telefono') }}"
                        class="@error('telefono') is-invalid @enderror">
                    @error('telefono')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit">Registrarse</button>
                <p class="auth-links">¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar Sesión</a></p>
            </form>
        </div>

    </div>


    

</body>

</html>
