<header class="bg-white border-bottom">

    <div class="container py-3">
        <div class="d-flex align-items-center gap-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="text-decoration-none text-dark">
                <span class="fs-3 fw-bold">Mr Bulls</span>
            </a>

            {{-- Buscador --}}
            <div class="flex-grow-1 d-none d-md-block">
                <div class="input-group">
                    <span class="input-group-text bg-transparent">
                        <i class="bx bx-search"></i>
                    </span>

                    <input type="search" class="form-control" placeholder="Buscar productos...">
                </div>
            </div>

            {{-- Usuario --}}
            @guest

                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                    <i class="bx bx-user me-1"></i>
                    Iniciar sesión
                </a>

            @else

                <div class="dropdown">

                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bx bx-user me-1"></i>

                        {{ auth()->user()->name }}
                    </button>


                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>

                            <span class="dropdown-item-text">

                                <small class="text-muted">
                                    Sesión iniciada como
                                </small>

                                <div class="fw-semibold">
                                    {{ auth()->user()->email }}
                                </div>

                            </span>

                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        {{-- Lo activaremos después --}}
                        <li>

                            <span class="dropdown-item text-muted">
                                <i class="bx bx-package me-2"></i>
                                Mis pedidos
                            </span>

                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>

                            <form method="POST" action="{{ route('logout') }}">

                                @csrf

                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bx bx-log-out me-2"></i>
                                    Cerrar sesión
                                </button>

                            </form>

                        </li>

                    </ul>

                </div>

            @endguest

            {{-- Carrito --}}
            <livewire:shop.cart-counter />

        </div>
    </div>

    {{-- Menú secundario --}}
    <nav class="border-top">
        <div class="container">
            <div class="d-flex gap-4 py-2">

                <a href="#" class="text-decoration-none text-body">
                    Inicio
                </a>

                <a href="{{ route('products.index') }}" class="text-decoration-none text-body">
                    Productos
                </a>

                <a href="#" class="text-decoration-none text-body">
                    Categorías
                </a>

                <a href="#" class="text-decoration-none text-body">
                    Ofertas
                </a>

            </div>
        </div>
    </nav>

</header>