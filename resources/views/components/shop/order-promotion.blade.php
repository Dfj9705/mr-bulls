@props(['item'])
@if(($item->promotion_discount ?? 0) > 0)
    <small class="d-block text-success">
        {{ $item->promotion_snapshot['name'] ?? 'Promoción' }}:
        ahorro Q{{ number_format($item->promotion_discount, 2) }} incluido en el subtotal.
    </small>
@endif
