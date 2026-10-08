<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: dejavusans, sans-serif;
                font-size: 10px;
                color: #222;
            }

            h1 {
                font-size: 22px;
                margin-bottom: 3px;
            }

            h2 {
                font-size: 13px;
                margin-top: 22px;
                margin-bottom: 10px;
            }

            .subtitle {
                color: #666;
                font-size: 10px;
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .summary td {
                padding: 10px;
                border: 1px solid #ddd;
                width: 50%;
            }

            .metric-label {
                font-size: 9px;
                color: #666;
            }

            .metric-value {
                font-size: 15px;
                font-weight: bold;
                margin-top: 5px;
            }

            .details th {
                background: #222;
                color: #fff;
                padding: 9px;
                text-align: left;
            }

            .details td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }

            .details tr:nth-child(even) td {
                background: #f5f5f5;
            }

            .right {
                text-align: right;
            }

            .total-row td {
                font-weight: bold;
                background: #e9e9e9;
            }

            .note {
                margin-top: 15px;
                font-size: 9px;
                color: #666;
            }
        </style>
    </head>

    <body>

        <h1>MR BULLS</h1>

        <div class="subtitle">
            Reporte de ventas<br>
            Período:
            {{ \Carbon\Carbon::parse($summary['date_from'])->format('d/m/Y') }}
            al
            {{ \Carbon\Carbon::parse($summary['date_to'])->format('d/m/Y') }}
            <br>
            Generado: {{ $generatedAt->format('d/m/Y H:i') }}
        </div>

        <table class="summary">
            <tr>
                <td>
                    <div class="metric-label">Ventas cobradas</div>
                    <div class="metric-value">
                        Q {{ number_format($summary['total_sales'], 2) }}
                    </div>
                </td>
                <td>
                    <div class="metric-label">Pedidos pagados</div>
                    <div class="metric-value">
                        {{ $summary['paid_orders'] }}
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="metric-label">Ticket promedio</div>
                    <div class="metric-value">
                        Q {{ number_format($summary['average_ticket'], 2) }}
                    </div>
                </td>
                <td>
                    <div class="metric-label">Pendiente de cobro</div>
                    <div class="metric-value">
                        Q {{ number_format($summary['pending_amount'], 2) }}
                    </div>
                </td>
            </tr>
        </table>

        <h2>Detalle de ventas diarias</h2>

        <table class="details">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="right">Pedidos pagados</th>
                    <th class="right">Total cobrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailySales as $day)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}
                        </td>
                        <td class="right">
                            {{ $day['orders'] }}
                        </td>
                        <td class="right">
                            Q {{ number_format($day['total'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align:center">
                            No hay ventas cobradas en este período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="right">
                        {{ $summary['paid_orders'] }}
                    </td>
                    <td class="right">
                        Q {{ number_format($summary['total_sales'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="note">
            Los importes incluyen costos de envío.
            Se consideran únicamente pedidos pagados y no cancelados,
            según la fecha de confirmación del pago registrada en el sistema.
        </div>

        @if ($summary['historical_paid_without_date'] > 0)
            <div class="note">
                Aviso: existen
                {{ $summary['historical_paid_without_date'] }}
                pedidos pagados sin fecha de pago registrada.
                No están incluidos en las ventas del período.
            </div>
        @endif

    </body>

</html>