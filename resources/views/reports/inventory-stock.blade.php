<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: dejavusans, sans-serif;
                font-size: 9px;
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
            }

            th {
                background: #222;
                color: white;
                padding: 8px;
                text-align: left;
            }

            td {
                padding: 7px;
                border-bottom: 1px solid #ddd;
            }

            .right {
                text-align: right;
            }

            .summary {
                margin: 15px 0;
            }

            .summary td {
                background: #f3f4f6;
                padding: 10px;
            }
        </style>
    </head>

    <body>
        <h1>MR BULLS</h1>
        <strong>Reporte de existencias actuales</strong>

        <p class="muted">
            Generado: {{ $generatedAt->format('d/m/Y H:i') }}
            <br>
            Estado: {{ $status }} |
            Búsqueda: {{ $search ?: 'Todos' }}
        </p>

        <table class="summary">
            <tr>
                <td>Productos: <strong>{{ $summary['total_products'] }}</strong></td>
                <td>Unidades: <strong>{{ $summary['total_units'] }}</strong></td>
                <td>Stock bajo: <strong>{{ $summary['low_stock_products'] }}</strong></td>
                <td>Agotados: <strong>{{ $summary['out_of_stock_products'] }}</strong></td>
            </tr>
        </table>

        <p>
            Valor potencial de venta:
            <strong>Q {{ number_format($summary['inventory_value'], 2) }}</strong>
        </p>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th>Categoría</th>
                    <th class="right">Stock</th>
                    <th class="right">Mínimo</th>
                    <th class="right">Precio</th>
                    <th class="right">Valor</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->category?->name ?? '-' }}</td>
                        <td class="right">{{ $product->stock }}</td>
                        <td class="right">{{ $product->minimum_stock }}</td>
                        <td class="right">Q {{ number_format($product->price, 2) }}</td>
                        <td class="right">
                            Q {{ number_format($product->stock * $product->price, 2) }}
                        </td>
                        <td>
                            @if ($product->stock === 0)
                                Agotado
                            @elseif ($product->stock <= $product->minimum_stock)
                                Stock bajo
                            @else
                                Disponible
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center">
                            No se encontraron productos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="muted">
            El valor del inventario se calcula con precios de venta,
            no con costos de adquisición.
            El resumen corresponde al inventario completo.
        </p>
    </body>

</html>