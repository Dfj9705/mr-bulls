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
                font-size: 20px;
                margin-bottom: 4px;
            }

            .subtitle {
                color: #666;
                margin-bottom: 16px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .summary td {
                width: 33%;
                background: #f3f4f6;
                padding: 12px;
                border: 2px solid #fff;
            }

            .metric {
                font-size: 15px;
                font-weight: bold;
                margin-top: 5px;
            }

            .details {
                margin-top: 18px;
            }

            .details th {
                background: #222;
                color: white;
                padding: 9px 6px;
                text-align: left;
            }

            .details td {
                padding: 8px 6px;
                border-bottom: 1px solid #ddd;
            }

            .details tr:nth-child(even) td {
                background: #f7f7f7;
            }

            .right {
                text-align: right;
            }

            .note {
                margin-top: 15px;
                font-size: 8px;
                color: #666;
            }
        </style>
    </head>

    <body>

        <h1>MR BULLS</h1>

        <div class="subtitle">
            <strong>Reporte de productos más vendidos</strong>
            <br>
            Período:
            {{ \Carbon\Carbon::parse($report['date_from'])->format('d/m/Y') }}
            al
            {{ \Carbon\Carbon::parse($report['date_to'])->format('d/m/Y') }}
            <br>
            Ordenado por:
            {{ $report['sort_by'] === 'revenue'
    ? 'Ingresos generados'
    : 'Unidades vendidas' }}
            <br>
            Generado: {{ $generatedAt->format('d/m/Y H:i') }}
        </div>

        <table class="summary">
            <tr>
                <td>
                    Unidades vendidas
                    <div class="metric">
                        {{ number_format($report['total_units']) }}
                    </div>
                </td>
                <td>
                    Ingresos por productos
                    <div class="metric">
                        Q {{ number_format($report['total_revenue'], 2) }}
                    </div>
                </td>
                <td>
                    Productos diferentes
                    <div class="metric">
                        {{ number_format($report['total_products']) }}
                    </div>
                </td>
            </tr>
        </table>

        <h2>Ranking de productos</h2>

        <table class="details">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th class="right">Pedidos</th>
                    <th class="right">Unidades</th>
                    <th class="right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['products'] as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['sku'] ?? '-' }}</td>
                        <td class="right">
                            {{ $product['orders_count'] }}
                        </td>
                        <td class="right">
                            {{ number_format($product['units_sold']) }}
                        </td>
                        <td class="right">
                            Q {{ number_format($product['revenue'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center">
                            No hay productos vendidos en este período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="note">
            Se incluyen únicamente productos de pedidos pagados
            y no cancelados, según la fecha de pago registrada.
            Los ingresos no incluyen costos de envío.

            @if (count($report['products']) < $report['total_products'])
                <br>
                El ranking muestra
                {{ count($report['products']) }}
                de {{ $report['total_products'] }}
                registros de productos vendidos.
                Los indicadores superiores corresponden al
                total del período.
            @endif
        </div>

    </body>

</html>