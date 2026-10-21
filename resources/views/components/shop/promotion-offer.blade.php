@props(['product'])
@php
    $offers = app(\App\Services\PricingService::class)->activePromotions()->filter(
        fn($offer) => (int) $offer->category_id === (int) $product->category_id
            && \App\Services\PricingService::cents($offer->eligible_price) === \App\Services\PricingService::cents($product->price)
    );
@endphp
@foreach($offers as $offer)
    <small class="d-block text-success fw-bold mt-1">
        {{ $offer->bundle_quantity }} por Q{{ number_format($offer->bundle_price, 2) }}.
    </small>
@endforeach