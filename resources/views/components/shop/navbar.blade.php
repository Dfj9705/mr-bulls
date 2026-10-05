<header class="bg-white border-bottom">

    <div class="container py-3">
        <div class="d-flex align-items-center gap-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}"
               class="text-decoration-none text-dark">
                <span class="fs-3 fw-bold">Mr Bulls</span>
            </a>

            {{-- Buscador --}}
            <div class="flex-grow-1 d-none d-md-block">
                <div class="input-group">
                    <span class="input-group-text bg-transparent">
                        <i class="bx bx-search"></i>
                    </span>

                    <input
                        type="search"
                        class="form-control"
                        placeholder="Buscar productos..."
                    >
                </div>
            </div>

            {{-- Usuario --}}
            <button
                type="button"
                class="btn btn-outline-secondary"
            >
                <i class="bx bx-user me-1"></i>
                Cuenta
            </button>

            {{-- Carrito --}}
            <button
                type="button"
                class="btn btn-primary position-relative"
            >
                <i class="bx bx-cart"></i>

                <span class="ms-1 d-none d-lg-inline">
                    Carrito
                </span>

                <span
                    class="position-absolute top-0 start-100
                           translate-middle badge rounded-pill bg-danger"
                >
                    0
                </span>
            </button>

        </div>
    </div>

    {{-- Menú secundario --}}
    <nav class="border-top">
        <div class="container">
            <div class="d-flex gap-4 py-2">

                <a href="#"
                   class="text-decoration-none text-body">
                    Inicio
                </a>

                <a href="#"
                   class="text-decoration-none text-body">
                    Productos
                </a>

                <a href="#"
                   class="text-decoration-none text-body">
                    Categorías
                </a>

                <a href="#"
                   class="text-decoration-none text-body">
                    Ofertas
                </a>

            </div>
        </div>
    </nav>

</header>