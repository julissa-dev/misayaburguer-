@if ($carritoItems->isEmpty() && $promocionItems->isEmpty())
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

    @foreach ($promocionItems as $promoItem)
        @if ($promoItem->promocion)
            {{-- Asegurarse de que la promoción existe --}}
            <div class="cart-item cart-promo" data-promo-id="{{ $promoItem->id }}">
                {{-- Puedes usar la imagen de la promoción si existe --}}
                @if ($promoItem->promocion->imagen_url)
                    <img src="{{ asset('storage/img/promociones/' . $promoItem->promocion->imagen_url) }}"
                        alt="{{ $promoItem->promocion->nombre }}">
                @else
                    {{-- O una imagen por defecto para promociones sin imagen --}}
                    <img src="{{ asset('img/default_promo.png') }}" alt="Promoción">
                @endif
                <div class="item-details">
                    <span class="item-name">{{ $promoItem->promocion->nombre }}</span>
                    <div class="item-quantity-controls">
                        <button class="quantity-btn-promo decrease-quantity-promo" data-promo-id="{{ $promoItem->id }}"
                            data-action="decrease">-</button>
                        <span class="item-quantity">{{ $promoItem->cantidad }}</span>
                        <button class="quantity-btn-promo increase-quantity-promo" data-promo-id="{{ $promoItem->id }}"
                            data-action="increase">+</button>
                    </div>
                    <span
                        class="item-price">${{ number_format($promoItem->promocion->precio_promocional * $promoItem->cantidad, 2) }}</span>
                    {{-- **SECCIÓN ELIMINADA: No se muestran los productos del combo aquí** --}}
                </div>
                <button class="remove-promo-btn" data-promo-id="{{ $promoItem->id }}">&times;</button>
            </div>
        @else
            <div class="cart-item">
                <div class="item-details">
                    <span class="item-name">Promoción no disponible</span>
                    <span class="item-quantity">Cantidad: {{ $promoItem->cantidad }}</span>
                    <span class="item-price">$0.00</span>
                </div>
                <button class="remove-item-btn" data-item-id="{{ $promoItem->id }}">&times;</button>
            </div>
        @endif
    @endforeach


@endif
