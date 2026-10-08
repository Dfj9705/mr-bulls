@extends('layouts.shop')

@section('title', 'Mr Bulls | Tienda')

@section('content')

    {{-- HERO --}}
    {{-- HERO --}}
    <section class="mr-bulls-hero">
        <div class="container">

            <div class="row align-items-center min-vh-hero">

                {{-- Contenido --}}
                <div class="col-lg-6 position-relative z-2">

                    <div class="hero-content py-5">

                        <span class="hero-eyebrow">
                            MR BULLS
                        </span>

                        <h1 class="hero-title">
                            Construye
                            <span>tu legado.</span>
                        </h1>

                        <p class="hero-description">
                            Diseños con carácter para quienes no pasan
                            desapercibidos. Descubre la colección de Mr Bulls
                            y encuentra el estilo que te representa.
                        </p>

                        <div class="d-flex flex-wrap gap-3 mt-4">

                            <a href="#productos-destacados" class="btn hero-btn-primary btn-lg">
                                Ver colección

                                <i class="bx bx-right-arrow-alt ms-2"></i>
                            </a>

                            <a href="{{ route('products.index') }}" class="btn hero-btn-secondary btn-lg">
                                Todos los productos
                            </a>

                        </div>

                        {{-- Detalle de marca --}}
                        <div class="hero-brand-line mt-5">

                            <span></span>

                            <small>
                                ESTILO · CARÁCTER · LEGADO
                            </small>

                        </div>

                    </div>

                </div>


                {{-- Imagen --}}
                <div class="col-lg-6">

                    <div class="hero-visual">

                        <div class="hero-image-wrapper">

                            <img src="{{ asset('images/hero-mrbulls.png') }}" alt="Colección Mr Bulls" class="hero-image">

                            {{-- <div class="hero-image-badge">
                                <strong>MR</strong>
                                <span>BULLS</span>
                            </div> --}}

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Decoración --}}
        <div class="hero-decoration hero-decoration-one"></div>
        <div class="hero-decoration hero-decoration-two"></div>

    </section>

    {{-- CATEGORÍAS --}}
    @if($categories->isNotEmpty())

        <section class="py-5 bg-white">
            <div class="container">

                <div class="d-flex justify-content-between
                                                    align-items-end mb-4">

                    <div>
                        <span class="text-primary fw-semibold">
                            Explora
                        </span>

                        <h2 class="fw-bold mb-0">
                            Categorías
                        </h2>
                    </div>

                </div>


                <div class="row g-4">

                    @foreach($categories as $category)

                        <div class="col-6 col-md-4 col-lg-2">

                            <div class="card h-100 border-0 shadow-sm
                                                                            category-card">

                                @if($category->image)

                                    <img src="{{ asset('storage/' . $category->image) }}" class="card-img-top category-image"
                                        alt="{{ $category->name }}">

                                @else

                                    <div class="category-image-placeholder">
                                        <i class="icon-base bx bx-category fs-1"></i>
                                    </div>

                                @endif

                                <div class="card-body text-center">

                                    <h6 class="card-title mb-0 fw-semibold">
                                        {{ $category->name }}
                                    </h6>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>
        </section>

    @endif


    {{-- PRODUCTOS DESTACADOS --}}
    <section id="productos-destacados" class="py-5">

        <div class="container">

            <div class="mb-4">

                <span class="text-primary fw-semibold">
                    Selección especial
                </span>

                <h2 class="fw-bold mb-0">
                    Productos destacados
                </h2>

            </div>


            @if($featuredProducts->isNotEmpty())

                <div class="row g-4">

                    @foreach($featuredProducts as $product)

                        <div class="col-6 col-md-4 col-lg-3">

                            <div class="card h-100 border-0 shadow-sm
                                                                            product-card">

                                <div class="position-relative">

                                    @if($product->main_image)

                                        <img src="{{ asset('storage/' . $product->main_image) }}" class="card-img-top product-image"
                                            alt="{{ $product->name }}">

                                    @else

                                        <div class="product-image-placeholder">
                                            <i class="icon-base bx bx-image fs-1"></i>
                                        </div>

                                    @endif


                                    @if(
                                            $product->compare_price &&
                                            $product->compare_price > $product->price
                                        )

                                        <span class="badge bg-danger
                                                                                                     position-absolute
                                                                                                     top-0 start-0 m-3">
                                            Oferta
                                        </span>

                                    @endif

                                </div>


                                <div class="card-body">

                                    <small class="text-muted">
                                        {{ $product->category->name }}
                                    </small>

                                    <h5 class="card-title mt-1 mb-2">

                                        <a href="{{ route('products.show', $product) }}"
                                            class="text-body text-decoration-none stretched-link">
                                            {{ $product->name }}
                                        </a>

                                    </h5>


                                    <div class="d-flex align-items-center gap-2">

                                        <span class="fw-bold fs-5">
                                            Q{{ number_format($product->price, 2) }}
                                        </span>

                                        @if(
                                                $product->compare_price &&
                                                $product->compare_price > $product->price
                                            )

                                            <span class="text-muted
                                                                                                         text-decoration-line-through">
                                                Q{{ number_format($product->compare_price, 2) }}
                                            </span>

                                        @endif

                                    </div>


                                    @if($product->stock <= 0)

                                        <small class="text-danger">
                                            Agotado
                                        </small>

                                    @elseif($product->stock <= 5)

                                        <small class="text-warning">
                                            Últimas unidades
                                        </small>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="text-center py-5">

                    <i class="icon-base bx bx-package fs-1 text-muted"></i>

                    <h5 class="mt-3">
                        Próximamente
                    </h5>

                    <p class="text-muted">
                        Estamos preparando nuestros productos destacados.
                    </p>

                </div>

            @endif

        </div>

    </section>

@endsection