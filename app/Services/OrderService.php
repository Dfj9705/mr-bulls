<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {

            /*
             * Volvemos a consultar y bloqueamos el pedido.
             */
            $order = Order::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->id);

            /*
             * Un pedido completado no puede cancelarse.
             */
            if ($order->status === Order::STATUS_COMPLETED) {
                throw ValidationException::withMessages([
                    'status' => 'Un pedido completado no puede cancelarse.',
                ]);
            }

            /*
             * Si ya está cancelado, no hacemos nada.
             */
            if ($order->status === Order::STATUS_CANCELLED) {
                return $order;
            }

            /*
             * Si todavía no se devolvió el stock,
             * lo restauramos.
             */


            if (is_null($order->stock_restored_at)) {

                foreach ($order->items as $item) {

                    /*
                     * El producto puede haber sido eliminado.
                     * order_items.product_id es nullable.
                     */
                    if (!$item->product_id) {
                        continue;
                    }

                    $product = Product::query()
                        ->lockForUpdate()
                        ->find($item->product_id);

                    if (!$product) {
                        continue;
                    }

                    $product->increment(
                        'stock',
                        $item->quantity
                    );
                }

                $order->stock_restored_at = now();
            }

            $order->status = Order::STATUS_CANCELLED;
            $order->save();

            $order->statusHistory()->create([
                'status' => Order::STATUS_CANCELLED,
                'changed_by' => auth()->id(),
                'notes' => 'Pedido cancelado desde administración.',
            ]);

            return $order->fresh('items');
        });
    }

    public function changeStatus(
        Order $order,
        string $newStatus,
        ?int $changedBy = null,
        ?string $notes = null
    ): Order {
        return DB::transaction(function () use ($order, $newStatus, $changedBy, $notes) {

            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            $allowedTransitions = [
                Order::STATUS_PENDING => [
                    Order::STATUS_PROCESSING,
                ],

                Order::STATUS_PROCESSING => [
                    Order::STATUS_SHIPPED,
                ],

                Order::STATUS_SHIPPED => [
                    Order::STATUS_COMPLETED,
                ],

                Order::STATUS_COMPLETED => [],
                Order::STATUS_CANCELLED => [],
            ];

            if ($order->status === $newStatus) {
                return $order;
            }

            if (
                !in_array(
                    $newStatus,
                    $allowedTransitions[$order->status] ?? [],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'No se puede cambiar el pedido de "%s" a "%s".',
                        $order->status,
                        $newStatus
                    ),
                ]);
            }

            $order->status = $newStatus;
            $order->save();

            $order->statusHistory()->create([
                'status' => $newStatus,
                'changed_by' => $changedBy,
                'notes' => $notes,
            ]);

            return $order->fresh([
                'items',
                'statusHistory',
            ]);
        });
    }
}