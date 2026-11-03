<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Support\GuatemalaLocations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Mail\OrderReceived;
use Illuminate\Support\Facades\Mail;

class AdminOrderCreationService
{
    public function create(array $data): Order
    {
        $validated = Validator::make($data, [
            'customer_type' => [
                'required',
                Rule::in(['registered', 'guest']),
            ],
            'user_id' => [
                Rule::requiredIf(
                    fn() => ($data['customer_type'] ?? null) === 'registered'
                ),
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_recipient' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:30'],
            'shipping_department' => ['required', 'string'],
            'shipping_municipality' => ['required', 'string'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_references' => ['nullable', 'string', 'max:1000'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'discount_type' => [
                'nullable',
                Rule::in(['fixed', 'percent']),
            ],
            'discount_value' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'discount_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ])->validate();

        $discountType = $validated['discount_type'] ?? null;
        $discountValue = (float) ($validated['discount_value'] ?? 0);

        $hasDiscount = filled($discountType) && $discountValue > 0;

        if ($hasDiscount && !Auth::user()?->can('pedidos.aplicar-descuento')) {
            throw ValidationException::withMessages([
                'discount_value' => 'No tienes permiso para aplicar descuentos.',
            ]);
        }

        if ($discountValue > 0 && !$discountType) {
            throw ValidationException::withMessages([
                'discount_type' => 'Selecciona el tipo de descuento.',
            ]);
        }

        if ($discountType === 'percent' && $discountValue > 100) {
            throw ValidationException::withMessages([
                'discount_value' => 'El descuento no puede superar el 100%.',
            ]);
        }

        if ($hasDiscount && blank($validated['discount_reason'] ?? null)) {
            throw ValidationException::withMessages([
                'discount_reason' => 'Debes indicar el motivo del descuento.',
            ]);
        }

        $department = $validated['shipping_department'];
        $municipality = $validated['shipping_municipality'];

        $departments = GuatemalaLocations::departments();
        $municipalities = GuatemalaLocations::municipalitiesFor($department);

        if (
            !array_key_exists($department, $departments) ||
            !array_key_exists($municipality, $municipalities)
        ) {
            throw ValidationException::withMessages([
                'shipping_municipality' =>
                    'Selecciona un departamento y municipio válidos.',
            ]);
        }

        $order = DB::transaction(function () use ($validated) {

            $items = collect($validated['items'])->values();

            $productIds = $items
                ->pluck('product_id')
                ->map(fn($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $orderItems = [];
            $subtotalCents = 0;

            foreach ($items as $index => $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                $product = $products->get($productId);

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Uno de los productos seleccionados ya no está disponible.',
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" =>
                            "Stock insuficiente para {$product->name}. " .
                            "Disponible: {$product->stock}. " .
                            "Solicitado: {$quantity}.",
                    ]);
                }

                // El precio se consulta en BD, nunca del formulario.
                $unitPriceCents = (int) round(
                    (float) $product->price * 100
                );

                $lineCents = $unitPriceCents * $quantity;
                $subtotalCents += $lineCents;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => number_format(
                        $unitPriceCents / 100,
                        2,
                        '.',
                        ''
                    ),
                    'subtotal' => number_format(
                        $lineCents / 100,
                        2,
                        '.',
                        ''
                    ),
                ];
            }

            $subtotal = number_format(
                $subtotalCents / 100,
                2,
                '.',
                ''
            );


            $discountType = $validated['discount_type'] ?? null;
            $discountValue = (float) ($validated['discount_value'] ?? 0);

            $discountCents = 0;

            if ($discountType === 'percent') {
                $discountCents = (int) round(
                    $subtotalCents * min($discountValue, 100) / 100,
                    0,
                    PHP_ROUND_HALF_UP
                );
            } elseif ($discountType === 'fixed') {
                $discountCents = (int) round(
                    $discountValue * 100,
                    0,
                    PHP_ROUND_HALF_UP
                );
            }

            if ($discountCents > $subtotalCents) {
                throw ValidationException::withMessages([
                    'discount_value' =>
                        'El descuento no puede superar el subtotal del pedido.',
                ]);
            }

            $totalCents = $subtotalCents - $discountCents;

            $manualDiscount = number_format(
                $discountCents / 100,
                2,
                '.',
                ''
            );

            $total = number_format(
                $totalCents / 100,
                2,
                '.',
                ''
            );


            $order = Order::create([
                'user_id' =>
                    $validated['customer_type'] === 'registered'
                    ? (int) $validated['user_id']
                    : null,

                'order_number' => 'TEMP-' . Str::uuid(),

                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],

                'shipping_recipient' => $validated['shipping_recipient'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_department' => $validated['shipping_department'],
                'shipping_municipality' => $validated['shipping_municipality'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_references' =>
                    $validated['shipping_references'] ?? null,

                'subtotal' => $subtotal,
                'shipping_cost' => '0.00',
                'wallet_discount' => '0.00',
                'promotion_discount' => '0.00',
                'total' => $total,

                'manual_discount' => $manualDiscount,

                'discount_type' => $discountCents > 0
                    ? $discountType
                    : null,

                'discount_value' => $discountCents > 0
                    ? $discountValue
                    : null,

                'discount_reason' => $discountCents > 0
                    ? $validated['discount_reason']
                    : null,

                'discount_applied_by' => $discountCents > 0
                    ? Auth::id()
                    : null,

                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,

                'customer_notes' =>
                    $validated['customer_notes'] ?? null,

                'public_token' => Str::random(64),
            ]);

            $order->update([
                'order_number' => sprintf(
                    'MB-%s-%06d',
                    now()->format('Ymd'),
                    $order->id
                ),
            ]);

            foreach ($orderItems as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];

                $stockBefore = (int) $product->stock;
                $stockAfter = $stockBefore - $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $quantity,
                    'subtotal' => $item['subtotal'],
                ]);

                $product->update([
                    'stock' => $stockAfter,
                ]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'type' => 'sale',
                    'quantity' => -$quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' =>
                        "Venta administrativa - {$order->order_number}",
                ]);
            }

            $order->statusHistory()->create([
                'status' => Order::STATUS_PENDING,
                'changed_by' => Auth::id(),
                'notes' => 'Pedido creado desde el panel administrativo.',
            ]);


            return $order;
        }, 3);

        /*
         * La transacción terminó correctamente.
         * Enviamos el correo de confirmación
         * utilizando la misma plantilla del checkout.
         */
        Mail::to($order->customer_email)
            ->queue(
                new OrderReceived(
                    $order->load('items')
                )
            );

        return $order;
    }
}
