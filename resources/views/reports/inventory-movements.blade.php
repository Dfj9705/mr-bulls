<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: dejavusans, sans-serif;
                font-size: 8px;
            }

            h1 {
                font-size: 19px;
                margin-bottom: 4px;
            }

            .muted {
                color: #666;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }

            th {
                background: #222;
                color: white;
                padding: 7px 5px;
                text-align: left;
            }

            td {
                padding: 6px 5px;
                border-bottom: 1px solid #ddd;
            }

            .right {
                text-align: right;
            }
        </style>
    </head>

    <body>
        <h1>MR BULLS</h1>
        <strong>Historial de movimientos de inventario</strong>

        <p class="muted">
            Período: {{ $dateFrom }} al {{ $dateTo }}
            <br>
            Tipo: {{ $type ?: 'Todos' }} |
            Producto ID: {{ $productId ?: 'Todos' }}
            <br>
            Generado: {{ $generatedAt->format('d/m/Y H:i') }}
        </p>

        <p>
            Movimientos encontrados:
            <strong>{{ number_format($summary['total_movements']) }}</strong>
            |
            Cantidad acumulada:
            <strong>{{ number_format($summary['total_quantity']) }}</strong>
        </p>

        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th class="right">Cantidad</th>
                    <th class="right">Antes</th>
                    <th class="right">Después</th>
                    <th>Pedido</th>
                    <th>Usuario</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td>{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->product?->name ?? 'Producto eliminado' }}</td>
                        <td>{{ $movement->type }}</td>
                        <td class="right">{{ $movement->quantity }}</td>
                        <td class="right">{{ $movement->stock_before }}</td>
                        <td class="right">{{ $movement->stock_after }}</td>
                        <td>{{ $movement->order?->order_number ?? '-' }}</td>
                        <td>{{ $movement->user?->name ?? 'Sistema' }}</td>
                        <td>{{ $movement->reason }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center">
                            No hay movimientos para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>

</html>