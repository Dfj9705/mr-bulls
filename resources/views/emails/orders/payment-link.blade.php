<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>
            Link de pago - {{ $order->order_number }}
        </title>
    </head>

    <body style="
    margin:0;
    padding:0;
    background:#f5f5f5;
    font-family:Arial, Helvetica, sans-serif;
    color:#333333;
">

        <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
            style="background:#f5f5f5; padding:30px 15px;">
            <tr>
                <td align="center">

                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="
                    max-width:600px;
                    background:#ffffff;
                    border-radius:10px;
                    overflow:hidden;
                ">

                        {{-- HEADER --}}
                        <tr>
                            <td align="center" style="
                            padding:30px;
                            background:#212529;
                            color:#ffffff;
                        ">
                                <div style="
                                font-size:26px;
                                font-weight:bold;
                            ">
                                    Mr Bulls
                                </div>
                            </td>
                        </tr>


                        {{-- CONTENIDO --}}
                        <tr>
                            <td style="padding:35px;">

                                <h1 style="
                                margin:0 0 20px;
                                font-size:24px;
                            ">
                                    Tu link de pago está disponible
                                </h1>

                                <p>
                                    Hola
                                    <strong>
                                        {{ $order->customer_name }}
                                    </strong>,
                                </p>

                                <p style="line-height:1.6;">
                                    Ya puedes realizar el pago de tu pedido.
                                    Utiliza el botón que aparece a continuación
                                    para continuar.
                                </p>


                                {{-- RESUMEN --}}
                                <table width="100%" cellpadding="0" cellspacing="0" style="
                                background:#f8f9fa;
                                border-radius:8px;
                                margin:25px 0;
                            ">
                                    <tr>

                                        <td style="padding:20px;">

                                            <div style="
                                            font-size:13px;
                                            color:#777;
                                            margin-bottom:5px;
                                        ">
                                                Número de pedido
                                            </div>

                                            <div style="
                                            font-size:18px;
                                            font-weight:bold;
                                        ">
                                                {{ $order->order_number }}
                                            </div>

                                        </td>


                                        <td align="right" style="padding:20px;">

                                            <div style="
                                            font-size:13px;
                                            color:#777;
                                            margin-bottom:5px;
                                        ">
                                                Total
                                            </div>

                                            <div style="
                                            font-size:20px;
                                            font-weight:bold;
                                        ">
                                                Q{{ number_format(
    $order->total,
    2
) }}
                                            </div>

                                        </td>

                                    </tr>
                                </table>


                                {{-- BOTÓN DE PAGO --}}
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
                                    <tr>
                                        <td align="center">

                                            <a href="{{ $order->payment_url }}" style="
                                            display:inline-block;
                                            background:#28a745;
                                            color:#ffffff;
                                            padding:15px 32px;
                                            border-radius:6px;
                                            text-decoration:none;
                                            font-weight:bold;
                                            font-size:16px;
                                        ">
                                                Pagar pedido
                                            </a>

                                        </td>
                                    </tr>
                                </table>


                                <p style="
                                color:#777;
                                font-size:13px;
                                line-height:1.6;
                            ">
                                    El botón anterior te llevará al sitio
                                    donde podrás realizar el pago correspondiente
                                    a tu pedido.
                                </p>


                                {{-- VER PEDIDO --}}
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
                                    <tr>
                                        <td align="center">

                                            <a href="{{ route(
    'orders.success',
    ['token' => $order->public_token]
) }}" style="
                                            display:inline-block;
                                            color:#212529;
                                            border:1px solid #212529;
                                            padding:12px 24px;
                                            border-radius:6px;
                                            text-decoration:none;
                                            font-weight:bold;
                                        ">
                                                Ver mi pedido
                                            </a>

                                        </td>
                                    </tr>
                                </table>


                                <div style="
                                margin-top:30px;
                                padding:15px;
                                background:#f8f9fa;
                                border-radius:6px;
                                color:#666;
                                font-size:13px;
                                line-height:1.6;
                            ">
                                    Si ya realizaste el pago de este pedido,
                                    puedes ignorar este mensaje.
                                </div>

                            </td>
                        </tr>


                        {{-- FOOTER --}}
                        <tr>
                            <td align="center" style="
                            padding:25px;
                            background:#f8f9fa;
                            color:#777;
                            font-size:12px;
                        ">
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