<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Mr Bulls')
    </title>

    @vite([
        'resources/css/shop/app.css',
        'resources/js/shop/app.js'
    ])

    @livewireStyles

    @stack('styles')
</head>

<body>

    @yield('content')

    @livewireScripts

    @stack('scripts')

</body>
</html>