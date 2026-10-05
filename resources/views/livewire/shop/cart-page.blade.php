<div>

    <section class="bg-white border-bottom">
        <div class="container py-5">

            <h1 class="fw-bold mb-2">
                Tu carrito
            </h1>

            <p class="text-muted mb-0">
                Revisa los productos antes de continuar con tu compra.
            </p>

        </div>
    </section>


    <section class="py-5">

        <div class="container">

            @if($items->isNotEmpty())

                <div class="row g-4">

                    {{-- PRODUCTOS --}}
                    <div class="col-lg-8">

                        <div class="card border-0 shadow-sm">

                            <div class="card-body">

                                @foreach($items as $item)

                                    @php
                                        $product = $item['product'];
                                    @endphp

                                    <div
                                        class="cart-item py-4
                                               {{ ! $loop->last ? 'border-bottom' : '' }}"
                                        wire:key="cart-product-{{ $product->id }}"
                                    >

                                        <div class="row align-items-center g-3">

                                            {{-- IMAGEN --}}
                                            <div class="col-3 col-md-2">

                                                <a
                                                    href="{{ route('products.show', $product) }}"
                                                >

                                                    @if($product->main_image)

                                                        <img
                                                            src="{{ asset('storage/' . $product->main_image) }}"
                                                            alt="{{ $product->name }}"
                                                            class="img-fluid rounded cart-product-image"
                                                        >

                                                    @else

                                                        <div class="cart-product-placeholder">
                                                            <i class="bx bx-image"></i>
                                                        </div>

                                                    @endif

                                                </a>

                                            </div>


                                            {{-- INFORMACIÓN --}}
                                            <div class="col-9 col-md-4">

                                                <small class="text-muted">
                                                    {{ $product->category->name }}
                                                </small>

                                                <h5 class="mb-1">

                                                    <a
                                                        href="{{ route('products.show', $product) }}"
                                                        class="text-body text-decoration-none"
                                                    >
                                                        {{ $product->name }}
                                                    </a>

                                                </h5>

                                                <small class="text-muted">
                                                    {{ $product->sku }}
                                                </small>

                                            </div>


                                            {{-- PRECIO --}}
                                            <div class="col-6 col-md-2">

                                                <small class="d-md-none text-muted d-block">
                                                    Precio
                                                </small>

                                                <span class="fw-semibold">
                                                    Q{{ number_format($product->price, 2) }}
                                                </span>

                                            </div>


                                            {{-- CANTIDAD --}}
                                            <div class="col-6 col-md-2">

                                                <div class="input-group input-group-sm">

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary"
                                                        wire:click="decrease({{ $product->id }})"
                                                        wire:loading.attr="disabled"
                                                    >
                                                        −
                                                    </button>

                                                    <input
                                                        type="text"
                                                        class="form-control text-center"
                                                        value="{{ $item['quantity'] }}"
                                                        readonly
                                                    >

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary"
                                                        wire:click="increase({{ $product->id }})"
                                                        wire:loading.attr="disabled"
                                                        @disabled($item['quantity'] >= $product->stock)
                                                    >
                                                        +
                                                    </button>

                                                </div>

                                            </div>


                                            {{-- SUBTOTAL --}}
                                            <div class="col-8 col-md-1">

                                                <small class="d-md-none text-muted d-block">
                                                    Subtotal
                                                </small>

                                                <span class="fw-bold">
                                                    Q{{ number_format($item['subtotal'], 2) }}
                                                </span>

                                            </div>


                                            {{-- ELIMINAR --}}
                                            <div class="col-4 col-md-1 text-end">

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    wire:click="remove({{ $product->id }})"
                                                    wire:confirm="¿Deseas eliminar este producto del carrito?"
                                                >
                                                    <i class="bx bx-trash"></i>
                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>


                        <div class="d-flex justify-content-between mt-3">

                            <a
                                href="{{ route('products.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                <i class="bx bx-left-arrow-alt me-1"></i>
                                Seguir comprando
                            </a>

                            <button
                                type="button"
                                class="btn btn-outline-danger"
                                wire:click="clear"
                                wire:confirm="¿Deseas vaciar todo el carrito?"
                            >
                                Vaciar carrito
                            </button>

                        </div>

                    </div>


                    {{-- RESUMEN --}}
                    <div class="col-lg-4">

                        <div
                            class="card border-0 shadow-sm"
                            style="position: sticky; top: 100px;"
                        >

                            <div class="card-body p-4">

                                <h4 class="fw-bold mb-4">
                                    Resumen
                                </h4>


                                <div class="d-flex justify-content-between mb-3">

                                    <span class="text-muted">
                                        Productos
                                    </span>

                                    <span>
                                        {{ $items->sum('quantity') }}
                                    </span>

                                </div>


                                <div class="d-flex justify-content-between mb-3">

                                    <span class="text-muted">
                                        Subtotal
                                    </span>

                                    <span>
                                        Q{{ number_format($subtotal, 2) }}
                                    </span>

                                </div>


                                <div class="d-flex justify-content-between mb-3">

                                    <span class="text-muted">
                                        Envío
                                    </span>

                                    <span class="text-muted">
                                        Por calcular
                                    </span>

                                </div>


                                <hr>


                                <div class="d-flex justify-content-between
                                            align-items-center mb-4">

                                    <span class="fw-bold fs-5">
                                        Total
                                    </span>

                                    <span class="fw-bold fs-4">
                                        Q{{ number_format($subtotal, 2) }}
                                    </span>

                                </div>


                                <button
                                    type="button"
                                    class="btn btn-primary btn-lg w-100"
                                    disabled
                                >
                                    Continuar compra
                                </button>

                                <small class="d-block text-muted text-center mt-2">
                                    El envío se calculará durante el checkout.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            @else

                {{-- CARRITO VACÍO --}}
                <div class="text-center py-5">

                    <div class="mb-4">
                        <i
                            class="bx bx-cart"
                            style="font-size: 5rem;"
                        ></i>
                    </div>

                    <h3 class="fw-bold">
                        Tu carrito está vacío
                    </h3>

                    <p class="text-muted mb-4">
                        Explora nuestros productos y encuentra algo para ti.
                    </p>

                    <a
                        href="{{ route('products.index') }}"
                        class="btn btn-primary btn-lg"
                    >
                        Ver productos
                    </a>

                </div>

            @endif

        </div>

    </section>

</div>