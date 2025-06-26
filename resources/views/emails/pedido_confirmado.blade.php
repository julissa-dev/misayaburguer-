<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido Confirmado</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f8f8; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px;">
        <h2>¡Hola {{ $usuario->nombre }}!</h2>
        <p>Gracias por tu pedido. Aquí tienes un resumen:</p>

        <h3>Pedido #{{ $pedido->id }}</h3>
        <ul>
            @foreach ($pedido->items as $item)
                <li>
                    {{ $item->producto->nombre }} x{{ $item->cantidad }} - 
                    S/. {{ number_format($item->cantidad * $item->precio_unit, 2) }}
                </li>
            @endforeach
        </ul>

        @php
            $promos = $pedido->items->filter(fn($i) => $i->promocion);
        @endphp

        @if ($promos->isNotEmpty())
            <h4>Promociones:</h4>
            <ul>
                @foreach ($promos as $promoItem)
                    <li>
                        {{ $promoItem->promocion->nombre }} 
                        ({{ $promoItem->cantidad }} items) -
                        S/. {{ number_format($promoItem->cantidad * $promoItem->precio_unit, 2) }}
                    </li>
                @endforeach
            </ul>
        @endif

        <p><strong>Dirección:</strong> {{ $pedido->direccion }}</p>
        <p><strong>Total:</strong> S/. {{ number_format($pedido->total, 2) }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($pedido->estado) }}</p>

        <p style="margin-top: 30px;">Pronto estaremos enviando tu pedido. ¡Gracias por elegirnos!</p>
        <p>– Saya Burguer 🍔</p>
    </div>
</body>
</html>
