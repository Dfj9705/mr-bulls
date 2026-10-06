<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Pedido {{ $order->order_number }}
    </title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f5f5f5;
    font-family:Arial, Helvetica, sans-serif;
    color:#333333;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
    style="background:#f5f5f5; padding:30px 15px;"
>
    <tr>
        <td align="center">

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                role="presentation"
                style="
                    max-width:600px;
                    background:#ffffff;
                    border-radius:10px;
                    overflow:hidden;
                "
            >

                {{-- HEADER --}}
                <tr>
                    <td
                        align="center"
                        style="
                            padding:30px;
                            background:#212529;
                            color:#ffffff;
                        "
                    >

                        <div
                            style="
                                font-size:26px;
                                font-weight:bold;
                            "
                        >
                            Mr Bulls
                        </div>

                    </td>
                </tr>


                {{-- CONTENIDO --}}
                <tr>
                    <td style="padding:35px;">

                        <h1
                            style="
                                margin:0 0 20px;
                                font-size:24px;
                            "
                        >
                            ¡Recibimos tu pedido!
                        </h1>

                        <p>
                            Hola
                            <strong>
                                {{ $order->customer_name }}
                            </strong>,
                        </p>

                        <p style="line-height:1.6;">
                            Tu pedido fue registrado correctamente.
                            Te mantendremos informado sobre su estado.
                        </p>


                        {{-- PEDIDO --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            style="
                                background:#f8f9fa;
                                border-radius:8px;
                                margin:25px 0;
                            "
                        >

                            <tr>
                                <td style="padding:20px;">

                                    <div
                                        style="
                                            font-size:13px;
                                            color:#777;
                                            margin-bottom:5px;
                                        "
                                    >
                                        Número de pedido
                                    </div>

                                    <div
                                        style="
                                            font-size:18px;
                                            font-weight:bold;
                                        "
                                    >
                                        {{ $order->order_number }}
                                    </div>

                                </td>

                                <td
                                    align="right"
                                    style="padding:20px;"
                                >

                                    <div
                                        style="
                                            font-size:13px;
                                            color:#777;
                                            margin-bottom:5px;
                                        "
                                    >
                                        Total
                                    </div>

                                    <div
                                        style="
                                            font-size:20px;
                                            font-weight:bold;
                                        "
                                    >
                                        Q{{ number_format($order->total, 2) }}
                                    </div>

                                </td>
                            </tr>

                        </table>


                        {{-- PRODUCTOS --}}
                        <h2
                            style="
                                font-size:18px;
                                margin-top:30px;
                            "
                        >
                            Productos
                        </h2>

                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                        >

                            @foreach($order->items as $item)

                                <tr>

                                    <td
                                        style="
                                            padding:15px 0;
                                            border-bottom:1px solid #eeeeee;
                                        "
                                    >

                                        <strong>
                                            {{ $item->product_name }}
                                        </strong>

                                        <div
                                            style="
                                                color:#777;
                                                font-size:13px;
                                                margin-top:5px;
                                            "
                                        >
                                            {{ $item->quantity }}
                                            ×
                                            Q{{ number_format(
                                                $item->unit_price,
                                                2
                                            ) }}
                                        </div>

                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding:15px 0;
                                            border-bottom:1px solid #eeeeee;
                                            font-weight:bold;
                                        "
                                    >
                                        Q{{ number_format(
                                            $item->subtotal,
                                            2
                                        ) }}
                                    </td>

                                </tr>

                            @endforeach

                        </table>


                        {{-- TOTALES --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            style="margin-top:20px;"
                        >

                            <tr>
                                <td style="padding:5px 0;">
                                    Subtotal
                                </td>

                                <td
                                    align="right"
                                    style="padding:5px 0;"
                                >
                                    Q{{ number_format(
                                        $order->subtotal,
                                        2
                                    ) }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:5px 0;">
                                    Envío
                                </td>

                                <td
                                    align="right"
                                    style="padding:5px 0;"
                                >
                                    Q{{ number_format(
                                        $order->shipping_cost,
                                        2
                                    ) }}
                                </td>
                            </tr>

                            <tr>
                                <td
                                    style="
                                        padding-top:15px;
                                        font-size:18px;
                                        font-weight:bold;
                                    "
                                >
                                    Total
                                </td>

                                <td
                                    align="right"
                                    style="
                                        padding-top:15px;
                                        font-size:20px;
                                        font-weight:bold;
                                    "
                                >
                                    Q{{ number_format(
                                        $order->total,
                                        2
                                    ) }}
                                </td>
                            </tr>

                        </table>


                        {{-- ENTREGA --}}
                        <h2
                            style="
                                font-size:18px;
                                margin-top:35px;
                            "
                        >
                            Información de entrega
                        </h2>

                        <p style="line-height:1.6;">

                            <strong>
                                {{ $order->shipping_recipient }}
                            </strong>

                            <br>

                            {{ $order->shipping_phone }}

                            <br>

                            {{ $order->shipping_address }}

                            <br>

                            {{ $order->shipping_municipality }},
                            {{ $order->shipping_department }}

                            @if($order->shipping_references)

                                <br><br>

                                <strong>Referencias:</strong>

                                {{ $order->shipping_references }}

                            @endif

                        </p>


                        {{-- PAGO --}}
                        @if($order->payment_url)

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                style="margin-top:30px;"
                            >
                                <tr>
                                    <td align="center">

                                        <a
                                            href="{{ $order->payment_url }}"
                                            style="
                                                display:inline-block;
                                                background:#28a745;
                                                color:#ffffff;
                                                padding:14px 28px;
                                                border-radius:6px;
                                                text-decoration:none;
                                                font-weight:bold;
                                            "
                                        >
                                            Pagar pedido
                                        </a>

                                    </td>
                                </tr>
                            </table>

                        @else

                            <div
                                style="
                                    margin-top:30px;
                                    padding:15px;
                                    background:#fff3cd;
                                    border-radius:6px;
                                    color:#664d03;
                                    line-height:1.5;
                                "
                            >
                                Estamos preparando la información
                                para que puedas realizar el pago.
                                Te enviaremos otro correo cuando
                                esté disponible.
                            </div>

                        @endif


                        {{-- VER PEDIDO --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            style="margin-top:20px;"
                        >
                            <tr>
                                <td align="center">

                                    <a
                                        href="{{ route(
                                            'orders.success',
                                            ['token' => $order->public_token]
                                        ) }}"
                                        style="
                                            display:inline-block;
                                            color:#212529;
                                            border:1px solid #212529;
                                            padding:12px 24px;
                                            border-radius:6px;
                                            text-decoration:none;
                                            font-weight:bold;
                                        "
                                    >
                                        Ver mi pedido
                                    </a>

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>


                {{-- FOOTER --}}
                <tr>
                    <td
                        align="center"
                        style="
                            padding:25px;
                            background:#f8f9fa;
                            color:#777;
                            font-size:12px;
                        "
                    >
                        © {{ date('Y') }} Mr Bulls

                        <br>

                        Gracias por comprar con nosotros.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>