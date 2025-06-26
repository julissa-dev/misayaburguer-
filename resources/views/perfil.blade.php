<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saya Burguer - Perfil</title>

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('css/usuario/perfil.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/header.css') }}" />

    <!-- Íconos -->
    <script src="https://kit.fontawesome.com/a2d4f54cbc.js" crossorigin="anonymous"></script>

    <!-- SweetAlert y animaciones -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .oculto {
            display: none;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    @include('partials.header')

    {{-- Contenido principal --}}
    <main>
        <section class="perfil-card">
            <div class="perfil-title">
                <div class="perfil-title-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h2>Mi Perfil</h2>
            </div>

            <div class="perfil-info">
                <div>
                    <span>Nombre:</span>
                    <p>
                        @if(empty(Auth::user()->nombre) && empty(Auth::user()->apellido))
                            <span class="no-registrado">No registrado</span>
                        @else
                            {{ Auth::user()->nombre }} {{ Auth::user()->apellido }}
                        @endif
                    </p>
                </div>
                <div>
                    <span>Email:</span>
                    <p>{{ Auth::user()->email }}</p>
                </div>
                <div>
                    <span>Teléfono:</span>
                    <p>
                        @if(empty(Auth::user()->telefono))
                            <span class="no-registrado">No registrado</span>
                        @else
                            {{ Auth::user()->telefono }}
                        @endif
                    </p>
                </div>
                <div>
                    <span>Dirección:</span>
                    <p>
                        @if(empty(Auth::user()->direccion))
                            <span class="no-registrado">No registrada</span>
                        @else
                            {{ Auth::user()->direccion }}
                        @endif
                    </p>
                </div>
                <div>
                    <span>Fecha de registro:</span>
                    <p>{{ Auth::user()->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <button onclick="document.getElementById('modalPerfil').classList.remove('oculto')" class="btn-editar">
                <i class="fas fa-edit animate-pulse"></i> Editar Perfil
            </button>
        </section>
    </main>

    {{-- Modal de edición --}}
    <div id="modalPerfil" class="modal oculto">
        <div class="modal-box">
            <button onclick="document.getElementById('modalPerfil').classList.add('oculto')" class="btn-cerrar">
                <i class="fas fa-times-circle"></i>
            </button>
            <h3 class="perfil-title">
                <i class="fas fa-user-edit"></i> Editar Perfil
            </h3>
            <form method="POST" action="{{ route('perfil.actualizar') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label>Nombre</label>
                    <input name="nombre" value="{{ Auth::user()->nombre }}" required>
                </div>

                <div>
                    <label>Apellido</label>
                    <input name="apellido" value="{{ Auth::user()->apellido }}" required>
                </div>

                <div>
                    <label>Email</label>
                    <input name="email" type="email" value="{{ Auth::user()->email }}" required>
                </div>

                <div>
                    <label>Teléfono</label>
                    <input name="telefono" value="{{ Auth::user()->telefono }}">
                </div>

                <div>
                    <label>Dirección</label>
                    <input name="direccion" value="{{ Auth::user()->direccion }}">
                </div>

                <div style="text-align: right; padding-top: 1rem;">
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    {{-- JS para rutas --}}
    <script>
        window.routes = {
            productosFiltrar: "{{ route('productos.filtrar') }}",
            perfil: "{{ route('perfil') }}",
            login: "{{ route('register') }}"
        };
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
</body>
</html>
