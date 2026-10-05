<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class AddToCart extends Component
{
    public Product $product;

    public int $quantity = 1;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function increase(): void
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decrease(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function add(CartService $cart): void
    {
        $this->product->refresh();

        if (
            !$this->product->is_active ||
            $this->product->stock <= 0
        ) {
            return;
        }

        $this->quantity = min(
            max(1, $this->quantity),
            $this->product->stock
        );

        $cart->add(
            $this->product,
            $this->quantity
        );

        $this->dispatch('cart-updated');

        $this->dispatch(
            'cart-message',
            message: 'Producto agregado al carrito.'
        );
    }

    public function render()
    {
        return view('livewire.shop.add-to-cart');
    }
}