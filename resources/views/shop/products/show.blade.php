@extends('layouts.shop')

@section('title', $product->name . ' | Mr Bulls')

@section(
    'meta_description',
    $product->short_description ?? 'Producto Mr Bulls'
)

@section('content')

    <section class="py-5">
        <div class="container">

            {{-- BREADCRUMB --}}
            <nav aria-label="breadcrumb" class="mb-4">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}" class="text-decoration-none">
                            Inicio
                        </a>
                    </li>

                    <li class="breadcrumb-item">
                        <a href="{{ route('products.index') }}" class="text-decoration-none">
                            Productos
                        </a>
                    </li>

                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $product->name }}
                    </li>

                </ol>

            </nav>


            <div class="row g-5">

                {{-- IMÁGENES --}}
                <div class="col-lg-6">

                    <div class="product-detail-main mb-3">

                        @if($product->main_image)

                            <img id="mainProductImage" src="{{ asset('storage/' . $product->main_image) }}"
                                alt="{{ $product->name }}" class="img-fluid">

                        @else

                            <div class="product-detail-placeholder">
                                <i class="bx bx-image"></i>
                            </div>

                        @endif

                    </div>


                    {{-- GALERÍA --}}
                    @if($product->main_image || $product->images->isNotEmpty())

                        <div class="product-gallery">

                            @if($product->main_image)

                                <button type="button" class="product-thumbnail active"
                                    data-image="{{ asset('storage/' . $product->main_image) }}">
                                    <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}">
                                </button>

                            @endif


                            @foreach($product->images as $image)

                                <button type="button" class="product-thumbnail"
                                    data-image="{{ asset('storage/' . $image->image) }}">
                                    <img src="{{ asset('storage/' . $image->image) }}" alt="{{ $product->name }}">
                                </button>

                            @endforeach

                        </div>

                    @endif

                </div>


                {{-- INFORMACIÓN --}}
                <div class="col-lg-6">

                    <span class="text-primary fw-semibold">
                        {{ $product->category->name }}
                    </span>

                    <h1 class="display-5 fw-bold mt-2 mb-3">
                        {{ $product->name }}
                    </h1>


                    {{-- SKU --}}
                    <div class="text-muted mb-3">
                        SKU: {{ $product->sku }}
                    </div>


                    {{-- PRECIO --}}
                    <div class="d-flex align-items-center gap-3 mb-4">

                        <span class="display-6 fw-bold mb-0">
                            Q{{ number_format($product->price, 2) }}
                        </span>


                        @if(
                                                $product->compare_price &&
                                                $product->compare_price > $product->price
                                            )

                                            <span class="fs-4 text-muted
                                                                                       text-decoration-line-through">
                                                Q{{ number_format(
                                $product->compare_price,
                                2
                            ) }}
                                            </span>

                        @endif

                    </div>


                    {{-- DESCRIPCIÓN CORTA --}}
                    @if($product->short_description)

                        <p class="lead text-muted">
                            {{ $product->short_description }}
                        </p>

                    @endif


                    <hr class="my-4">


                    {{-- STOCK --}}
                    <div class="mb-4">

                        @if($product->stock <= 0)

                            <span class="badge bg-danger fs-6">
                                Agotado
                            </span>

                        @elseif($product->stock <= 5)

                            <span class="badge bg-warning fs-6">
                                Últimas {{ $product->stock }} unidades
                            </span>

                        @else

                            <span class="badge bg-success fs-6">
                                Disponible
                            </span>

                        @endif

                    </div>


                    {{-- CANTIDAD --}}
                    @if($product->stock > 0)

                        <livewire:shop.add-to-cart :product="$product" />

                    @else

                        <button type="button" class="btn btn-secondary btn-lg w-100" disabled>
                            Producto agotado
                        </button>

                    @endif

                </div>

            </div>


            {{-- DESCRIPCIÓN COMPLETA --}}
            @if($product->description)

                <div class="row mt-5 pt-4">

                    <div class="col-lg-8">

                        <h3 class="fw-bold mb-4">
                            Descripción
                        </h3>

                        <div class="product-description">
                            {!! $product->description !!}
                        </div>

                    </div>

                </div>

            @endif

            <livewire:product-interactions :product="$product" :key="'product-interactions-' . $product->id" />

        </div>
    </section>

@endsection


@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /*
             * Galería
             */
            const mainImage = document.getElementById('mainProductImage');
            const thumbnails = document.querySelectorAll('.product-thumbnail');

            thumbnails.forEach((thumbnail) => {

                thumbnail.addEventListener('click', () => {

                    if (!mainImage) {
                        return;
                    }

                    mainImage.src = thumbnail.dataset.image;

                    thumbnails.forEach((item) => {
                        item.classList.remove('active');
                    });

                    thumbnail.classList.add('active');
                });

            });


            /*
             * Cantidad
             */
            const quantity = document.getElementById('quantity');
            const increase = document.getElementById('increaseQuantity');
            const decrease = document.getElementById('decreaseQuantity');

            if (!quantity) {
                return;
            }

            increase?.addEventListener('click', () => {

                const current = parseInt(quantity.value) || 1;
                const max = parseInt(quantity.max);

                if (current < max) {
                    quantity.value = current + 1;
                }

            });

            decrease?.addEventListener('click', () => {

                const current = parseInt(quantity.value) || 1;

                if (current > 1) {
                    quantity.value = current - 1;
                }

            });

            quantity.addEventListener('change', () => {

                let value = parseInt(quantity.value) || 1;
                const max = parseInt(quantity.max);

                value = Math.max(1, value);
                value = Math.min(value, max);

                quantity.value = value;

            });

        });
    </script>

@endpush