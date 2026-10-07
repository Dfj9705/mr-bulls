<?php

namespace App\Livewire\Shop;

use App\Models\Address;
use App\Models\InventoryMovement;
use App\Services\CartService;
use App\Support\GuatemalaLocations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Mail\OrderReceived;
use Illuminate\Support\Facades\Mail;
use Str;
class Checkout extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Comprador
    |--------------------------------------------------------------------------
    */

    public string $customerName = '';

    public string $customerEmail = '';

    public string $customerPhone = '';

    public string $shippingRecipient = '';

    public string $shippingPhone = '';

    public array $checkoutQuantities = [];

    public array $checkoutPrices = [];

    /*
    |--------------------------------------------------------------------------
    | Dirección
    |--------------------------------------------------------------------------
    */

    public ?int $selectedAddressId = null;

    public bool $useNewAddress = true;

    public string $department = '';

    public string $municipality = '';

    public string $address = '';

    public string $references = '';


    /*
    |--------------------------------------------------------------------------
    | Pedido
    |--------------------------------------------------------------------------
    */

    public string $customerNotes = '';


    public function mount(CartService $cart): void
    {
        /*
         * No tiene sentido entrar al checkout
         * con el carrito vacío.
         */
        $items = $cart->items();

        if ($items->isEmpty()) {
            $this->redirectRoute('cart.index');

            return;
        }

        $this->checkoutQuantities = $items
            ->mapWithKeys(fn($item) => [
                $item['product']->id => $item['quantity'],
            ])
            ->all();

        $this->checkoutPrices = $items
            ->mapWithKeys(fn($item) => [
                $item['product']->id => (string) $item['product']->price,
            ])
            ->all();

        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        $this->customerName = $user->name;
        $this->customerEmail = $user->email;
        $this->shippingRecipient = $user->name;

        /*
         * Buscamos dirección predeterminada.
         */
        $defaultAddress = $user->addresses()
            ->where('is_default', true)
            ->first();

        if ($defaultAddress) {

            $this->selectedAddressId = $defaultAddress->id;

            $this->useNewAddress = false;

            $this->loadAddress($defaultAddress);
        }
    }


    public function selectAddress(int $addressId): void
    {
        if (!Auth::check()) {
            return;
        }

        $address = Auth::user()
            ->addresses()
            ->find($addressId);

        if (!$address) {
            return;
        }

        $this->selectedAddressId = $address->id;

        $this->useNewAddress = false;

        $this->loadAddress($address);
    }


    public function useNewAddressForm(): void
    {
        $this->selectedAddressId = null;

        $this->useNewAddress = true;

        $this->department = '';
        $this->municipality = '';
        $this->address = '';
        $this->references = '';

        $this->shippingRecipient = $this->customerName;
        $this->shippingPhone = '';
    }


    public function updatedDepartment(): void
    {
        /*
         * Evitamos conservar un municipio de
         * otro departamento.
         */
        $this->municipality = '';
    }


    private function loadAddress(Address $address): void
    {
        $this->shippingRecipient = $address->recipient_name;
        $this->shippingPhone = $address->phone;

        $this->department = $address->department;
        $this->municipality = $address->municipality;
        $this->address = $address->address;
        $this->references = $address->references ?? '';
    }


    public function render(CartService $cart)
    {
        $addresses = collect();

        if (Auth::check()) {
            $addresses = Auth::user()
                ->addresses()
                ->orderByDesc('is_default')
                ->latest()
                ->get();
        }

        return view('livewire.shop.checkout', [
            'items' => $cart->items(),

            'subtotal' => $cart->subtotal(),

            'addresses' => $addresses,

            'departments' => GuatemalaLocations::departments(),

            'municipalities' =>
                GuatemalaLocations::municipalitiesFor(
                    $this->department
                ),
        ]);
    }

    protected function rules(): array
    {
        return [
            'customerName' => [
                'required',
                'string',
                'max:255',
            ],

            'customerEmail' => [
                'required',
                'email',
                'max:255',
            ],

            'customerPhone' => [
                'required',
                'string',
                'max:30',
            ],

            'shippingRecipient' => [
                'required',
                'string',
                'max:255',
            ],

            'shippingPhone' => [
                'required',
                'string',
                'max:30',
            ],

            'department' => [
                'required',
                'string',
            ],

            'municipality' => [
                'required',
                'string',
            ],

            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'references' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'customerNotes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'customerName.required' =>
                'Ingresa el nombre del comprador.',

            'customerEmail.required' =>
                'Ingresa tu correo electrónico.',

            'customerEmail.email' =>
                'Ingresa un correo electrónico válido.',

            'customerPhone.required' =>
                'Ingresa el teléfono del comprador.',

            'shippingRecipient.required' =>
                'Ingresa el nombre de quien recibirá el pedido.',

            'shippingPhone.required' =>
                'Ingresa el teléfono de entrega.',

            'department.required' =>
                'Selecciona un departamento.',

            'municipality.required' =>
                'Selecciona un municipio.',

            'address.required' =>
                'Ingresa la dirección de entrega.',
        ];
    }

    public function createOrder(CartService $cart)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validar datos del checkout
        |--------------------------------------------------------------------------
        */

        $validated = $this->validate();

        /*
        |--------------------------------------------------------------------------
        | 2. Obtener carrito actual
        |--------------------------------------------------------------------------
        */

        $cartItems = $cart->items();

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Tu carrito está vacío.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Detectar cambios en el carrito desde que inició checkout
        |--------------------------------------------------------------------------
        */

        $currentQuantities = $cartItems
            ->mapWithKeys(fn($item) => [
                $item['product']->id => $item['quantity'],
            ])
            ->all();

        if ($currentQuantities != $this->checkoutQuantities) {
            throw ValidationException::withMessages([
                'cart' =>
                    'La disponibilidad de uno o más productos cambió. '
                    . 'Revisa tu carrito antes de continuar.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Crear pedido dentro de una transacción
        |--------------------------------------------------------------------------
        */

        $order = DB::transaction(function () use ($cartItems, $validated) {

            /*
             * Bloqueamos los productos mientras procesamos
             * el pedido para evitar ventas simultáneas
             * sobre el mismo stock.
             */
            $productIds = $cartItems
                ->pluck('product.id')
                ->map(fn($id) => (int) $id)
                ->all();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');


            /*
            |--------------------------------------------------------------------------
            | 4. Volver a validar productos y calcular subtotal
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            $orderItems = [];

            foreach ($this->checkoutQuantities as $productId => $quantity) {

                $product = $products->get((int) $productId);

                $quantity = (int) $quantity;

                if (!$product) {
                    throw ValidationException::withMessages([
                        'cart' =>
                            'Uno de los productos de tu carrito ya no está disponible.',
                    ]);
                }

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' =>
                            "El producto {$product->name} ya no está disponible.",
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' =>
                            "La disponibilidad de {$product->name} cambió. "
                            . "Solicitaste {$quantity} y actualmente "
                            . "solo hay {$product->stock} disponible(s). "
                            . "Revisa tu carrito antes de continuar.",
                    ]);
                }

                $expectedPrice = $this->checkoutPrices[$product->id] ?? null;

                if (
                    $expectedPrice === null ||
                    bccomp(
                        (string) $product->price,
                        (string) $expectedPrice,
                        2
                    ) !== 0
                ) {
                    throw ValidationException::withMessages([
                        'cart' =>
                            "El precio de {$product->name} cambió. "
                            . 'Revisa tu carrito antes de continuar.',
                    ]);
                }

                $unitPrice = (float) $product->price;

                $itemSubtotal = round(
                    $unitPrice * $quantity,
                    2
                );

                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                ];
            }


            $subtotal = round($subtotal, 2);


            /*
            |--------------------------------------------------------------------------
            | 5. Envío
            |--------------------------------------------------------------------------
            |
            | Por ahora queda en Q0.
            | Después agregaremos las reglas de envío.
            |
            */

            $shippingCost = 0;

            $total = $subtotal + $shippingCost;


            /*
            |--------------------------------------------------------------------------
            | 6. Crear pedido
            |--------------------------------------------------------------------------
            */

            $order = Order::create([

                'user_id' => Auth::id(),

                /*
                 * Temporal.
                 *
                 * Después de obtener el ID real del pedido
                 * construiremos el número definitivo.
                 */
                'order_number' => 'TEMP-' . uniqid(),

                /*
                 * Comprador
                 */
                'customer_name' => $validated['customerName'],
                'customer_email' => $validated['customerEmail'],
                'customer_phone' => $validated['customerPhone'],

                /*
                 * Entrega
                 */
                'shipping_recipient' =>
                    $validated['shippingRecipient'],

                'shipping_phone' =>
                    $validated['shippingPhone'],

                'shipping_department' =>
                    $validated['department'],

                'shipping_municipality' =>
                    $validated['municipality'],

                'shipping_address' =>
                    $validated['address'],

                'shipping_references' =>
                    $validated['references'] ?? null,

                /*
                 * Totales
                 */
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,

                /*
                 * Estados iniciales
                 */
                'status' => Order::STATUS_PENDING,

                'payment_status' =>
                    Order::PAYMENT_PENDING,

                'payment_method' => null,
                'payment_url' => null,

                'customer_notes' =>
                    $validated['customerNotes'] ?? null,

                'public_token' => Str::random(64),
            ]);


            /*
            |--------------------------------------------------------------------------
            | 7. Número público del pedido
            |--------------------------------------------------------------------------
            */

            $order->update([
                'order_number' => sprintf(
                    'MB-%s-%06d',
                    now()->format('Ymd'),
                    $order->id
                ),
            ]);


            /*
            |--------------------------------------------------------------------------
            | 8. Crear items y descontar stock
            |--------------------------------------------------------------------------
            */

            foreach ($orderItems as $item) {

                $order->items()->create($item);

                $product = $products->get(
                    $item['product_id']
                );

                $stockBefore = $product->stock;
                $quantity = $item['quantity'];
                $stockAfter = $stockBefore - $quantity;

                $product->decrement(
                    'stock',
                    $quantity
                );

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'type' => 'sale',
                    'quantity' => -$quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' => "Venta - Pedido {$order->order_number}",
                ]);
            }

            $order->statusHistory()->create([
                'status' => Order::STATUS_PENDING,
            ]);


            return $order;
        });


        /*
        |--------------------------------------------------------------------------
        | 9. La transacción terminó correctamente
        |--------------------------------------------------------------------------
        |
        | Vaciar el carrito SOLO después del commit.
        |
        */

        $cart->clear();

        $this->dispatch('cart-updated');

        /*
        |--------------------------------------------------------------------------
        | 9. Enviar correo de confirmación
        |--------------------------------------------------------------------------
        */

        Mail::to($order->customer_email)
            ->queue(
                new OrderReceived(
                    $order->load('items')
                )
            );


        /*
        |--------------------------------------------------------------------------
        | 10. Ir a confirmación
        |--------------------------------------------------------------------------
        */

        return $this->redirectRoute(
            'orders.success',
            [
                'token' => $order->public_token,
            ],
            navigate: true
        );
    }
}