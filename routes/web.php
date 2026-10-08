<?php

use App\Http\Controllers\Shop\AuthController;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tienda pública
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    $categories = Category::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->limit(6)
        ->get();

    $featuredProducts = Product::query()
        ->with('category')
        ->where('is_active', true)
        ->where('is_featured', true)
        ->orderByDesc('created_at')
        ->limit(8)
        ->get();

    return view('shop.home', compact(
        'categories',
        'featuredProducts'
    ));

})->name('home');


/*
|--------------------------------------------------------------------------
| Productos
|--------------------------------------------------------------------------
*/

Route::view('/productos', 'shop.products.index')
    ->name('products.index');

Route::get('/productos/{product:slug}', function (Product $product) {

    abort_unless($product->is_active, 404);

    $product->load([
        'category',
        'images',
    ]);

    return view(
        'shop.products.show',
        compact('product')
    );

})->name('products.show');


/*
|--------------------------------------------------------------------------
| Carrito y checkout
|--------------------------------------------------------------------------
*/

Route::view('/carrito', 'shop.cart.index')
    ->name('cart.index');

Route::view('/checkout', 'shop.checkout.index')
    ->name('checkout.index');


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get(
        '/login',
        [AuthController::class, 'showLogin']
    )->name('login');

    Route::post(
        '/login',
        [AuthController::class, 'login']
    )->name('login.store');

    Route::get(
        '/registro',
        [AuthController::class, 'showRegister']
    )->name('register');

    Route::post(
        '/registro',
        [AuthController::class, 'register']
    )->name('register.store');

});

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Consulta pública del pedido
|--------------------------------------------------------------------------
|
| Esta ruta utiliza public_token para permitir consultar el pedido
| después de una compra, incluso cuando fue realizada como invitado.
|
*/

Route::get('/pedido/{token}/confirmado', function (string $token) {

    $order = Order::query()
        ->with([
            'items',
            'statusHistory',
        ])
        ->where('public_token', $token)
        ->firstOrFail();

    return view(
        'shop.orders.success',
        compact('order')
    );

})->name('orders.success');


/*
|--------------------------------------------------------------------------
| Mi cuenta
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Perfil
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/mi-cuenta/perfil',
        'shop.account.profile'
    )->name('account.profile');


    /*
    |--------------------------------------------------------------------------
    | Pedidos
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/mi-cuenta/pedidos',
        'shop.account.orders'
    )->name('account.orders');


    /*
    |--------------------------------------------------------------------------
    | Detalle de pedido
    |--------------------------------------------------------------------------
    |
    | Además del middleware auth, verificamos que el pedido realmente
    | pertenezca al usuario autenticado.
    |
    */

    Route::get('/mi-cuenta/pedidos/{order}', function (Order $order) {

        abort_unless(
            $order->user_id === auth()->id(),
            403
        );

        $order->load([
            'items.product',
            'statusHistory',
        ]);

        return view(
            'shop.account.order-show',
            compact('order')
        );

    })->name('account.orders.show');


    /**
     * Direcciones guardadas
     */
    Route::view(
        '/mi-cuenta/direcciones',
        'shop.account.addresses'
    )->name('account.addresses');

});