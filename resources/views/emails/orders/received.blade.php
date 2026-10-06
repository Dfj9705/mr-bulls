<x-mail::message>
    # ¡Recibimos tu pedido!

    Hola **{{ $order->customer_name }}**,

    Tu pedido fue registrado correctamente en **Mr Bulls**.

    <x-mail::panel>
        **Pedido:** {{ $order->order_number }}

        **Total:** Q{{ number_format($order->total, 2) }}

        **Estado:** Pendiente
    </x-mail::panel>

    ## Productos

    @foreach($order->items as $item)
        **{{ $item->product_name }}**
        {{ $item->quantity }} × Q{{ number_format($item->unit_price, 2) }}
        — **Q{{ number_format($item->subtotal, 2) }}**

    @endforeach

    ---

    **Subtotal:** Q{{ number_format($order->subtotal, 2) }}
    **Envío:** Q{{ number_format($order->shipping_cost, 2) }}
    **Total:** **Q{{ number_format($order->total, 2) }}**

    ## Entrega

    **Recibe:** {{ $order->shipping_recipient }}
    **Teléfono:** {{ $order->shipping_phone }}

    {{ $order->shipping_address }}
    {{ $order->shipping_municipality }}, {{ $order->shipping_department }}

    @if($order->shipping_references)
        **Referencias:** {{ $order->shipping_references }}
    @endif

    @if($order->payment_url)
        <x-mail::button :url="$order->payment_url">
            Pagar pedido
        </x-mail::button>
    @else
        En cuanto esté disponible la información para realizar el pago, te enviaremos otro correo.
    @endif

    <x-mail::button :url="route('orders.success', ['token' => $order->public_token])">
        Ver mi pedido
    </x-mail::button>

    Gracias por comprar en Mr Bulls.
</x-mail::message>