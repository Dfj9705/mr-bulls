<header class="shop-header bg-white">

    {{-- Navbar principal --}}
    <div class="container">
        <div class="d-flex align-items-center gap-3 gap-lg-4 py-3">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="navbar-brand flex-shrink-0 m-0" aria-label="Mr Bulls">
                <img src="{{ asset('images/logo-mrbulls.png') }}" alt="Mr Bulls" class="navbar-logo">
            </a>


            {{-- Buscador desktop --}}
            <div class="flex-grow-1 d-none d-md-block">
                <div class="navbar-search">

                    <i class="bx bx-search"></i>

                    <input type="search" class="form-control" placeholder="¿Qué estás buscando?"
                        aria-label="Buscar productos">

                </div>
            </div>


            {{-- Acciones --}}
            <div class="d-flex align-items-center gap-2 ms-auto">

                {{-- Usuario --}}
                @guest

                    <a href="{{ route('login') }}" class="navbar-action" title="Iniciar sesión">
                        <i class="bx bx-user"></i>

                        <span class="d-none d-lg-inline">
                            Ingresar
                        </span>
                    </a>

                @else

                    <div class="dropdown">

                        <button type="button" class="navbar-action" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-user"></i>

                            <span class="d-none d-lg-block text-start">
                                <small>Hola,</small>
                                <strong>
                                    {{ Str::limit(auth()->user()->name, 15) }}
                                </strong>
                            </span>

                            <i class="bx bx-chevron-down d-none d-lg-inline"></i>
                        </button>


                        <ul class="dropdown-menu dropdown-menu-end account-dropdown">

                            {{-- Datos del usuario --}}
                            <li>
                                <div class="px-3 py-2">

                                    <small class="text-muted">
                                        Sesión iniciada como
                                    </small>

                                    <div class="fw-bold text-dark">
                                        {{ auth()->user()->name }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ auth()->user()->email }}
                                    </div>

                                </div>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            {{-- Perfil --}}
                            <li>
                                <a href="{{ route('account.profile') }}" class="dropdown-item">
                                    <i class="bx bx-user-circle"></i>

                                    <span>
                                        Mi perfil
                                    </span>
                                </a>
                            </li>


                            {{-- Pedidos --}}
                            <li>
                                <a href="{{ route('account.orders') }}" class="dropdown-item">
                                    <i class="bx bx-package"></i>

                                    <span>
                                        Mis pedidos
                                    </span>
                                </a>
                            </li>


                            {{-- Direcciones --}}
                            <li>
                                <a href="{{ route('account.addresses') }}" class="dropdown-item">
                                    <i class="bx bx-map"></i>

                                    <span>
                                        Mis direcciones
                                    </span>
                                </a>
                            </li>


                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            {{-- Logout --}}
                            <li>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-log-out"></i>

                                        <span>
                                            Cerrar sesión
                                        </span>
                                    </button>

                                </form>

                            </li>

                        </ul>

                    </div>

                @endguest


                {{-- Carrito --}}
                <div class="navbar-cart">
                    <livewire:shop.cart-counter />
                </div>

            </div>

        </div>


        {{-- Buscador móvil --}}
        <div class="d-md-none pb-3">

            <div class="navbar-search">

                <i class="bx bx-search"></i>

                <input type="search" class="form-control" placeholder="Buscar productos..."
                    aria-label="Buscar productos">

            </div>

        </div>

    </div>


    {{-- Navegación --}}
    <nav class="shop-navigation">
        <div class="container">

            <div class="shop-navigation-inner">

                <a href="{{ route('home') }}" class="shop-nav-link
                        {{ request()->routeIs('home') ? 'active' : '' }}">
                    Inicio
                </a>

                <a href="{{ route('products.index') }}" class="shop-nav-link
                        {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    Productos
                </a>

                <a href="#" class="shop-nav-link">
                    Categorías
                </a>

                <a href="#" class="shop-nav-link">
                    Ofertas
                </a>

            </div>

        </div>
    </nav>

</header>