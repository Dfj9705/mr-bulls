@props(['item'])
@if(($item['promotion_discount_cents'] ?? 0) > 0)
    <small class="d-block text-success">
        {{ $item['promotion_snapshot']['name'] }}:
        ahorro Q{{ number_format($item['promotion_discount_cents'] / 100, 2) }} en esta línea.
        El subtotal ya incluye el descuento.
    </small>
@endif
