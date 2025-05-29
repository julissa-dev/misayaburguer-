<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
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

        <div id="login-form-container">
            <h1 class="form-header">Iniciar Sesión</h1>
            <form id="login-form" action="{{ route('login.store') }}" method="POST">
                @csrf

                {{-- Mensaje de error general para credenciales inválidas (opcional) --}}
                @if (session('error'))
                    <span class="error-message general-error">{{ session('error') }}</span>
                @endif

                <div class="form-group">
                    <label for="login-email">Correo:</label>
                    <input type="text" id="login-email" name="email" value="{{ old('email') }}"
                        class="@error('email') is-invalid @enderror">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="login-password">Contraseña:</label>
                    <input type="password" id="login-password" name="password"
                        class="@error('password') is-invalid @enderror">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit">Iniciar Sesión</button>
                <p class="auth-links">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
            </form>
        </div>

    </div>

</body>

</html>
