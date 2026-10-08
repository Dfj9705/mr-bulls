
<x-filament-panels::page>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach ([
            ['Productos', $summary['total_products'] ?? 0],
            ['Unidades', $summary['total_units'] ?? 0],
            ['Disponibles', $summary['available_products'] ?? 0],
            ['Stock bajo', $summary['low_stock_products'] ?? 0],
            ['Agotados', $summary['out_of_stock_products'] ?? 0],
            [
                'Valor potencial de venta',
                'Q ' . number_format($summary['inventory_value'] ?? 0, 2)
            ],
        ] as [$label, $value])
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $label }}
                </div>
                <div class="mt-2 text-2xl font-bold dark:text-white">
                    {{ is_numeric($value) ? number_format($value) : $value }}
                </div>
            </x-filament::section>
        @endforeach
    </div>

    <x-filament::section>
        <x-slot name="heading">
            Existencias actuales
        </x-slot>

        <form wire:submit="loadInventory" class="space-y-4">
            {{ $this->inventoryForm }}

            <x-filament::button type="submit">
                Consultar inventario
            </x-filament::button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="p-3">Producto</th>
                        <th class="p-3">SKU</th>
                        <th class="p-3">Categoría</th>
                        <th class="p-3 text-right">Stock</th>
                        <th class="p-3 text-right">Mínimo</th>
                        <th class="p-3 text-right">Precio</th>
                        <th class="p-3 text-right">Valor</th>
                        <th class="p-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b dark:border-gray-700">
                            <td class="p-3">{{ $product['name'] }}</td>
                            <td class="p-3">{{ $product['sku'] }}</td>
                            <td class="p-3">{{ $product['category'] }}</td>
                            <td class="p-3 text-right">{{ $product['stock'] }}</td>
                            <td class="p-3 text-right">{{ $product['minimum_stock'] }}</td>
                            <td class="p-3 text-right">
                                Q {{ number_format($product['price'], 2) }}
                            </td>
                            <td class="p-3 text-right">
                                Q {{ number_format($product['value'], 2) }}
                            </td>
                            <td class="p-3">
                                @if ($product['status'] === 'out')
                                    <x-filament::badge color="danger">
                                        Agotado
                                    </x-filament::badge>
                                @elseif ($product['status'] === 'low')
                                    <x-filament::badge color="warning">
                                        Stock bajo
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="success">
                                        Disponible
                                    </x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center">
                                No se encontraron productos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Historial de movimientos
        </x-slot>

        <form wire:submit="loadMovements" class="space-y-4">
            {{ $this->movementForm }}

            <x-filament::button type="submit">
                Consultar movimientos
            </x-filament::button>
        </form>

        <div class="mt-5 text-sm dark:text-gray-300">
            Movimientos encontrados:
            <strong>{{ number_format($movementSummary['total_movements'] ?? 0) }}</strong>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Producto</th>
                        <th class="p-3">Tipo</th>
                        <th class="p-3 text-right">Cantidad</th>
                        <th class="p-3 text-right">Antes</th>
                        <th class="p-3 text-right">Después</th>
                        <th class="p-3">Pedido</th>
                        <th class="p-3">Usuario</th>
                        <th class="p-3">Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr class="border-b dark:border-gray-700">
                            <td class="p-3">{{ $movement['date'] }}</td>
                            <td class="p-3">{{ $movement['product'] }}</td>
                            <td class="p-3">{{ $movement['type'] }}</td>
                            <td class="p-3 text-right">{{ $movement['quantity'] }}</td>
                            <td class="p-3 text-right">{{ $movement['stock_before'] }}</td>
                            <td class="p-3 text-right">{{ $movement['stock_after'] }}</td>
                            <td class="p-3">{{ $movement['order'] }}</td>
                            <td class="p-3">{{ $movement['user'] }}</td>
                            <td class="p-3">{{ $movement['reason'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center">
                                No hay movimientos en el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (($movementSummary['total_movements'] ?? 0) > 100)
            <p class="mt-3 text-sm text-gray-500">
                Se muestran los 100 movimientos más recientes.
            </p>
        @endif
    </x-filament::section>

</x-filament-panels::page>
