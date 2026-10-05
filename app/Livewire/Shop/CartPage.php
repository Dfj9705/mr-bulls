<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\On;

class CartPage extends Component
{
    public function increase(int $productId, CartService $cart): void
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $item = $cart->items()
            ->first(fn($item) => $item['product']->id === $productId);

        if (!$item) {
            return;
        }

        $cart->update(
            $product,
            $item['quantity'] + 1
        );

        $this->dispatch('cart-updated');
    }

    public function decrease(int $productId, CartService $cart): void
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $item = $cart->items()
            ->first(fn($item) => $item['product']->id === $productId);

        if (!$item) {
            return;
        }

        $cart->update(
            $product,
            $item['quantity'] - 1
        );

        $this->dispatch('cart-updated');
    }

    public function remove(int $productId, CartService $cart): void
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $cart->remove($product);

        $this->dispatch('cart-updated');
    }

    public function clear(CartService $cart): void
    {
        $cart->clear();

        $this->dispatch('cart-updated');
    }

    public function render(CartService $cart)
    {
        return view('livewire.shop.cart-page', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        // El render posterior obtiene nuevamente
        // los datos desde CartService.
    }
}