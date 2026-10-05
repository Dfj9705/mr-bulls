<?php

use App\Models\Category;
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