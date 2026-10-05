<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(CartService $cart): void
    {
        $this->refreshCart($cart);
    }

    #[On('cart-updated')]
    public function refreshCart(CartService $cart): void
    {
        $this->count = $cart->count();
    }

    public function remove(int $productId, CartService $cart): void
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $cart->remove($product);

        $this->refreshCart($cart);

        /*
         * También notificamos a otros componentes,
         * como CartPage si está montado.
         */
        $this->dispatch('cart-updated');
    }

    public function render(CartService $cart)
    {
        return view('livewire.shop.cart-counter', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
        ]);
    }
}