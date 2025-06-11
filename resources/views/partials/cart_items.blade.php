@if ($carritoItems->isEmpty())
    <p class="empty-cart-message">Tu carrito está vacío.</p>
@else
    @foreach ($carritoItems as $item)
        @if ($item->producto)
            <div class="cart-item" data-item-id="{{ $item->id }}">
                <img src="{{ asset('storage/img/productos/' . $item->producto->imagen_url) }}"
                    alt="{{ $item->producto->nombre }}">
                <div class="item-details">
                    <span class="item-name">{{ $item->producto->nombre }}</span>
                    <div class="item-quantity-controls">
                        <button class="quantity-btn decrease-quantity" data-item-id="{{ $item->id }}"
                            data-action="decrease">-</button>
                        <span class="item-quantity">{{ $item->cantidad }}</span>
                        <button class="quantity-btn increase-quantity" data-item-id="{{ $item->id }}"
                            data-action="increase">+</button>
                    </div>
                    <span class="item-price">${{ number_format($item->producto->precio * $item->cantidad, 2) }}</span>
                </div>
                <button class="remove-item-btn" data-item-id="{{ $item->id }}">&times;</button>
            </div>
        @else
            <div class="cart-item" data-item-id="{{ $item->id }}">
                <div class="item-details">
                    <span class="item-name">Producto no disponible</span>
                    <span class="item-quantity">Cantidad: {{ $item->cantidad }}</span>
                    <span class="item-price">$0.00</span>
                </div>
                <button class="remove-item-btn" data-item-id="{{ $item->id }}">&times;</button>
            </div>
        @endif
    @endforeach

    


@endif
