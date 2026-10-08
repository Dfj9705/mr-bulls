<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <link rel="shortcut icon" href="{{ asset('images/logo-corto-mrbulls.png') }}" type="image/x-icon">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>
            @yield('title', 'Mr Bulls')
        </title>

        @vite([
            'resources/assets/css/demo.css',
            'resources/assets/vendor/fonts/iconify/iconify.css',
            'resources/scss/app.scss',
            'resources/css/shop/app.css',
            'resources/js/shop/app.js',
        ])

        @livewireStyles

        @stack('styles')
    </head>

    <body>

        <div class="d-flex flex-column min-vh-100">

            <x-shop.navbar />

            <main class="flex-grow-1">
                @yield('content')
            </main>

            <x-shop.footer />

        </div>

        @livewireScripts
        @stack('scripts')

    </body>

</html>