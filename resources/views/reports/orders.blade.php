<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: dejavusans, sans-serif;
                font-size: 9px;
                color: #222;
            }

            h1 {
                font-size: 19px;
                margin-bottom: 4px;
            }

            .subtitle {
                color: #666;
                font-size: 10px;
                margin-bottom: 18px;
            }

            .summary {
                margin-bottom: 15px;
            }

            .summary td {
                padding: 7px 12px;
                background-color: #f3f4f6;
            }

            table.orders {
                width: 100%;
                border-collapse: collapse;
            }

            .orders th {
                background-color: #222;
                color: #fff;
                padding: 8px 5px;
                text-align: left;
            }

            .orders td {
                padding: 7px 5px;
                border-bottom: 1px solid #ddd;
            }

            .orders tr:nth-child(even) td {
                background-color: #f7f7f7;
            }

            .right {
                text-align: right;
            }

            .footer-total {
                font-weight: bold;
                background-color: #eee;
            }
        </style>
    </head>

    <body>

        <h1>MR BULLS</h1>

        <div class="subtitle">
            Reporte general de pedidos
            |
            Generado: {{ $generatedAt->format('d/m/Y H:i') }}
        </div>

        <div style="margin-bottom: 12px; font-size: 9px;">
            <strong>Período:</strong>
            {{ !empty($filters['date_from'])
    ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y')
    : 'Inicio' }}
            al
            {{ !empty($filters['date_to'])
    ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y')
    : 'Actualidad' }}

            |
            <strong>Estado:</strong>
            {{ match ($filters['status'] ?? null) {
    'pending' => 'Pendiente',
    'processing' => 'Procesando',
    'shipped' => 'Enviado',
    'completed' => 'Completado',
    'cancelled' => 'Cancelado',
    default => 'Todos'
} }}

            |
            <strong>Pago:</strong>
            {{ match ($filters['payment_status'] ?? null) {
    'pending' => 'Pendiente',
    'paid' => 'Pagado',
    'failed' => 'Fallido',
    'cancelled' => 'Cancelado',
    default => 'Todos'
} }}
        </div>

        <table class="summary">
            <tr>
                <td>
                    <strong>Total de pedidos:</strong>
                    {{ $totalOrders }}
                </td>
                <td>
                    <strong>Total cobrado:</strong>
                    Q {{ number_format($totalAmount, 2) }}
                </td>
            </tr>
        </table>

        <table class="orders">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Departamento</th>
                    <th>Municipio</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Link de pago</th>
                    <th>Método</th>
                    <th class="right">Subtotal</th>
                    <th class="right">Envío</th>
                    <th class="right">Total</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>
                                        {{ $order->created_at->format('d/m/Y') }}
                                    </td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->shipping_department }}</td>
                                    <td>{{ $order->shipping_municipality }}</td>
                                    <td>{{ $order->shipping_address }}</td>
                                    <td>
                                        {{ match ($order->status) {
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'shipped' => 'Enviado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $order->status
                    } }}
                                    </td>
                                    <td>
                                        {{ match ($order->payment_status) {
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        default => $order->payment_status
                    } }}
                                    </td>
                                    <td>{{ $order->payment_url }}</td>
                                    <td>{{ $order->payment_method }}</td>
                                    <td class="right">
                                        Q {{ number_format($order->subtotal, 2) }}
                                    </td>
                                    <td class="right">
                                        Q {{ number_format($order->shipping_cost, 2) }}
                                    </td>
                                    <td class="right">
                                        Q {{ number_format($order->total, 2) }}
                                    </td>
                                </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;">
                            No hay pedidos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </body>

</html>