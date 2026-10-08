<?php

use App\Http\Controllers\Shop\AuthController;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

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

Route::view('/productos', 'shop.products.index')
    ->name('products.index');

Route::get('/productos/{product:slug}', function (Product $product) {

    abort_unless($product->is_active, 404);

    $product->load([
        'category',
        'images',
    ]);

    return view('shop.products.show', compact('product'));

})->name('products.show');

Route::view('/carrito', 'shop.cart.index')
    ->name('cart.index');

Route::view('/checkout', 'shop.checkout.index')
    ->name('checkout.index');


Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    Route::get('/registro', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/registro', [AuthController::class, 'register'])
        ->name('register.store');

});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

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


Route::view(
    '/mi-cuenta/pedidos',
    'shop.account.orders'
)
    ->middleware('auth')
    ->name('account.orders');

Route::get('/mi-cuenta/pedidos/{order}', function (Order $order) {

    abort_unless(
        $order->user_id === auth()->id(),
        403
    );

    $order->load([
        'items',
        'statusHistory',
    ]);

    return view(
        'shop.account.order-show',
        compact('order')
    );

})
    ->middleware('auth')
    ->name('account.orders.show');