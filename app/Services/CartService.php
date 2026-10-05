<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function items(): Collection
    {
        $cart = session()->get(self::SESSION_KEY, []);

        if (empty($cart)) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $normalizedCart = [];

        $items = collect($cart)
            ->map(function ($quantity, $productId) use ($products, &$normalizedCart) {

                $product = $products->get((int) $productId);

                /*
                 * El producto ya no existe, está inactivo
                 * o se quedó sin stock.
                 */
                if (
                    !$product ||
                    !$product->is_active ||
                    $product->stock <= 0
                ) {
                    return null;
                }

                /*
                 * Nunca permitimos más unidades
                 * que el stock disponible actualmente.
                 */
                $quantity = min(
                    max(1, (int) $quantity),
                    $product->stock
                );

                /*
                 * Guardamos la versión normalizada
                 * que debe permanecer en sesión.
                 */
                $normalizedCart[$product->id] = $quantity;

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => (float) $product->price * $quantity,
                ];
            })
            ->filter()
            ->values();

        /*
         * Sincronizamos la sesión con la realidad
         * actual de la base de datos.
         */
        if ($normalizedCart !== $cart) {
            session()->put(self::SESSION_KEY, $normalizedCart);
        }

        return $items;
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $product->refresh();

        if (
            !$product->is_active ||
            $product->stock <= 0
        ) {
            return;
        }

        $quantity = max(1, $quantity);

        $cart = session()->get(self::SESSION_KEY, []);

        $currentQuantity = (int) ($cart[$product->id] ?? 0);

        $cart[$product->id] = min(
            $currentQuantity + $quantity,
            $product->stock
        );

        session()->put(self::SESSION_KEY, $cart);
    }

    public function update(Product $product, int $quantity): void
    {
        $product->refresh();

        $cart = session()->get(self::SESSION_KEY, []);

        if (!array_key_exists($product->id, $cart)) {
            return;
        }

        if ($quantity <= 0) {
            $this->remove($product);

            return;
        }

        if (
            !$product->is_active ||
            $product->stock <= 0
        ) {
            $this->remove($product);

            return;
        }

        $cart[$product->id] = min(
            $quantity,
            $product->stock
        );

        session()->put(self::SESSION_KEY, $cart);
    }

    public function remove(Product $product): void
    {
        $cart = session()->get(self::SESSION_KEY, []);

        unset($cart[$product->id]);

        session()->put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return $this->items()->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('subtotal');
    }
}