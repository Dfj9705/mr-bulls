<x-mail::message>
    # Tu link de pago está disponible

    Hola **{{ $order->customer_name }}**,

    Ya puedes realizar el pago correspondiente al pedido:

    <x-mail::panel>
        **Pedido:** {{ $order->order_number }}

        **Total:** Q{{ number_format($order->total, 2) }}
    </x-mail::panel>

    <x-mail::button :url="$order->payment_url">
        Pagar pedido
    </x-mail::button>

    También puedes consultar el estado de tu pedido:

    <x-mail::button :url="route('orders.success', ['token' => $order->public_token])">
        Ver mi pedido
    </x-mail::button>

    Si ya realizaste el pago, puedes ignorar este mensaje.

    Gracias,
    **Mr Bulls**
</x-mail::message>