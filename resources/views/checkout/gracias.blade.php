@if (session('mensaje'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Gracias!',
            text: '{{ session('mensaje') }}'
        });
    </script>
@endif