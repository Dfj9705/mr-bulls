<div>

    {{-- CABECERA --}}
    <section class="bg-white border-bottom">
        <div class="container py-5">

            <h1 class="fw-bold mb-2">
                Productos
            </h1>

            <p class="text-muted mb-0">
                Explora todos nuestros productos.
            </p>

        </div>
    </section>


    <section class="py-5">

        <div class="container">

            {{-- FILTROS --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3 align-items-end">

                        {{-- BÚSQUEDA --}}
                        <div class="col-lg-5">

                            <label class="form-label">
                                Buscar
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bx bx-search"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Buscar productos..."
                                    wire:model.live.debounce.400ms="search"
                                >

                            </div>

                        </div>


                        {{-- CATEGORÍA --}}
                        <div class="col-md-6 col-lg-3">

                            <label class="form-label">
                                Categoría
                            </label>

                            <select
                                class="form-select"
                                wire:model.live="category"
                            >

                                <option value="">
                                    Todas
                                </option>

                                @foreach($categories as $categoryItem)

                                    <option value="{{ $categoryItem->id }}">
                                        {{ $categoryItem->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- ORDEN --}}
                        <div class="col-md-6 col-lg-3">

                            <label class="form-label">
                                Ordenar
                            </label>

                            <select
                                class="form-select"
                                wire:model.live="sort"
                            >

                                <option value="latest">
                                    Más recientes
                                </option>

                                <option value="price_asc">
                                    Menor precio
                                </option>

                                <option value="price_desc">
                                    Mayor precio
                                </option>

                                <option value="name">
                                    Nombre
                                </option>

                            </select>

                        </div>


                        {{-- LIMPIAR --}}
                        <div class="col-lg-1">

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                wire:click="clearFilters"
                                title="Limpiar filtros"
                            >
                                <i class="bx bx-reset"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            {{-- INFORMACIÓN --}}
            <div class="d-flex justify-content-between
                        align-items-center mb-4">

                <span class="text-muted">

                    {{ $products->total() }}

                    {{ $products->total() === 1
                        ? 'producto'
                        : 'productos' }}

                </span>

                <div
                    wire:loading
                    wire:target="search,category,sort"
                    class="text-primary"
                >
                    Buscando...
                </div>

            </div>


            {{-- PRODUCTOS --}}
            <div
                wire:loading.class="opacity-50"
                wire:target="search,category,sort"
            >

                @if($products->count())

                    <div class="row g-4">

                        @foreach($products as $product)

                            <div
                                class="col-12 col-md-4 col-lg-3"
                                wire:key="product-{{ $product->id }}"
                            >

                                <div class="card h-100 border-0 shadow-sm product-card">

                                    <div class="position-relative">

                                        @if($product->main_image)

                                            <img
                                                src="{{ asset('storage/' . $product->main_image) }}"
                                                class="card-img-top product-image"
                                                alt="{{ $product->name }}"
                                            >

                                        @else

                                            <div class="product-image-placeholder">

                                                <i class="bx bx-image fs-1"></i>

                                            </div>

                                        @endif


                                        @if(
                                            $product->compare_price &&
                                            $product->compare_price > $product->price
                                        )

                                            <span
                                                class="badge bg-danger
                                                       position-absolute
                                                       top-0 start-0 m-3"
                                            >
                                                Oferta
                                            </span>

                                        @endif

                                    </div>


                                    <div class="card-body">

                                        <small class="text-muted">
                                            {{ $product->category->name }}
                                        </small>

                                        <h5 class="card-title mt-1 mb-2">

                                            <a
                                                href="{{ route('products.show', $product) }}"
                                                class="text-body text-decoration-none stretched-link"
                                            >
                                                {{ $product->name }}
                                            </a>

                                        </h5>


                                        <div class="d-flex flex-wrap
                                                    align-items-center gap-2">

                                            <span class="fw-bold fs-5">

                                                Q{{ number_format(
                                                    $product->price,
                                                    2
                                                ) }}

                                            </span>


                                            @if(
                                                $product->compare_price &&
                                                $product->compare_price >
                                                $product->price
                                            )

                                                <span
                                                    class="text-muted
                                                           text-decoration-line-through"
                                                >
                                                    Q{{ number_format(
                                                        $product->compare_price,
                                                        2
                                                    ) }}
                                                </span>

                                            @endif

                                        </div>


                                        <div class="mt-2">

                                            @if($product->stock <= 0)

                                                <small class="text-danger fw-semibold">
                                                    Agotado
                                                </small>

                                            @elseif($product->stock <= 5)

                                                <small class="text-warning fw-semibold">
                                                    Últimas {{ $product->stock }}
                                                    unidades
                                                </small>

                                            @else

                                                <small class="text-success">
                                                    Disponible
                                                </small>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    {{-- PAGINACIÓN --}}
                    <div class="mt-5">

                        {{ $products->links() }}

                    </div>

                @else

                    <div class="text-center py-5">

                        <i class="bx bx-search fs-1 text-muted"></i>

                        <h5 class="mt-3">
                            No encontramos productos
                        </h5>

                        <p class="text-muted">
                            Intenta cambiar los filtros de búsqueda.
                        </p>

                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="btn btn-outline-primary"
                        >
                            Limpiar filtros
                        </button>

                    </div>

                @endif

            </div>

        </div>

    </section>

</div>