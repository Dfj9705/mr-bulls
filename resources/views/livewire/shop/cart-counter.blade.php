<div class="dropdown">

    <button
        type="button"
        class="btn btn-primary position-relative"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false"
    >
        <i class="bx bx-cart me-1"></i>

        Carrito

        @if($count > 0)

            <span
                class="position-absolute top-0 start-100
                       translate-middle badge rounded-pill bg-danger"
            >
                {{ $count > 99 ? '99+' : $count }}
            </span>

        @endif

    </button>


    <div
        class="dropdown-menu dropdown-menu-end p-0 shadow border-0"
        style="width: min(380px, 90vw);"
    >

        <div class="p-3 border-bottom">

            <div class="d-flex justify-content-between align-items-center">

                <span class="fw-bold">
                    Mi carrito
                </span>

                <small class="text-muted">
                    {{ $count }}
                    {{ $count === 1 ? 'producto' : 'productos' }}
                </small>

            </div>

        </div>


        @if($items->isNotEmpty())

            <div
                class="p-3"
                style="max-height: 350px; overflow-y: auto;"
            >

                @foreach($items as $item)

                    @php
                        $product = $item['product'];
                    @endphp

                    <div
                        class="d-flex gap-3 py-2
                               {{ ! $loop->last ? 'border-bottom' : '' }}"
                        wire:key="mini-cart-{{ $product->id }}"
                    >

                        {{-- IMAGEN --}}
                        <a
                            href="{{ route('products.show', $product) }}"
                            class="flex-shrink-0"
                        >

                            @if($product->main_image)

                                <img
                                    src="{{ asset('storage/' . $product->main_image) }}"
                                    alt="{{ $product->name }}"
                                    width="65"
                                    height="65"
                                    class="rounded object-fit-cover"
                                >

                            @else

                                <div
                                    class="rounded bg-light d-flex
                                           align-items-center justify-content-center"
                                    style="width: 65px; height: 65px;"
                                >
                                    <i class="bx bx-image"></i>
                                </div>

                            @endif

                        </a>


                        {{-- INFORMACIÓN --}}
                        <div class="flex-grow-1 min-w-0">

                            <a
                                href="{{ route('products.show', $product) }}"
                                class="text-body text-decoration-none fw-semibold"
                            >
                                {{ $product->name }}
                            </a>

                            <div class="small text-muted mt-1">
                                {{ $item['quantity'] }}
                                ×
                                Q{{ number_format($product->price, 2) }}
                            </div>

                            <div class="fw-semibold">
                                Q{{ number_format($item['subtotal'], 2) }}
                            </div>

                        </div>


                        {{-- ELIMINAR --}}
                        <div>

                            <button
                                type="button"
                                class="btn btn-sm btn-icon btn-text-secondary"
                                wire:click="remove({{ $product->id }})"
                                title="Eliminar"
                            >
                                <i class="bx bx-x fs-5"></i>
                            </button>

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- RESUMEN --}}
            <div class="p-3 border-top">

                <div class="d-flex justify-content-between mb-3">

                    <span class="fw-semibold">
                        Subtotal
                    </span>

                    <span class="fw-bold">
                        Q{{ number_format($subtotal, 2) }}
                    </span>

                </div>

                <a
                    href="{{ route('cart.index') }}"
                    class="btn btn-primary w-100"
                >
                    Ver carrito
                </a>

            </div>

        @else

            <div class="text-center p-5">

                <i
                    class="bx bx-cart text-muted"
                    style="font-size: 3rem;"
                ></i>

                <p class="fw-semibold mt-3 mb-1">
                    Tu carrito está vacío
                </p>

                <small class="text-muted">
                    Agrega algunos productos para comenzar.
                </small>

                <div class="mt-3">

                    <a
                        href="{{ route('products.index') }}"
                        class="btn btn-sm btn-outline-primary"
                    >
                        Ver productos
                    </a>

                </div>

            </div>

        @endif

    </div>

</div>