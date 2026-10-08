<x-filament-panels::page>

    <x-filament::section>
        <x-slot name="heading">
            Filtros del reporte
        </x-slot>

        <form wire:submit="loadReport">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                    Generar reporte
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Unidades vendidas
            </div>
            <div class="mt-2 text-2xl font-bold dark:text-white">
                {{ number_format($report['total_units'] ?? 0) }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Ingresos por productos
            </div>
            <div class="mt-2 text-2xl font-bold dark:text-white">
                Q {{ number_format($report['total_revenue'] ?? 0, 2) }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Productos diferentes
            </div>
            <div class="mt-2 text-2xl font-bold dark:text-white">
                {{ number_format($report['total_products'] ?? 0) }}
            </div>
        </x-filament::section>

    </div>

    <x-filament::section>
        <x-slot name="heading">
            Ranking de productos
        </x-slot>

        @php
            $products = $report['products'] ?? [];

            $sortBy = $report['sort_by'] ?? 'quantity';

            $metric = $sortBy === 'revenue'
                ? 'revenue'
                : 'units_sold';

            $maxValue = collect($products)
                ->max($metric) ?: 1;
        @endphp

        @forelse ($products as $index => $product)
            @php
                $percentage = (
                    $product[$metric] / $maxValue
                ) * 100;
            @endphp

            <div class="mb-5">
                <div class="mb-2 flex items-center justify-between gap-4">
                    <div class="text-sm font-medium dark:text-white">
                        {{ $index + 1 }}.
                        {{ $product['name'] }}
                        <span class="text-gray-500">
                            ({{ $product['sku'] ?? 'Sin SKU' }})
                        </span>
                    </div>

                    <div class="text-sm font-semibold dark:text-white">
                        @if ($sortBy === 'revenue')
                            Q {{ number_format($product['revenue'], 2) }}
                        @else
                            {{ number_format($product['units_sold']) }} uds.
                        @endif
                    </div>
                </div>

                <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full rounded-full bg-primary-600" style="width: {{ $percentage }}%">
                    </div>
                </div>
            </div>
        @empty
            <div class="py-8 text-center text-gray-500">
                No hay productos vendidos en el período seleccionado.
            </div>
        @endforelse
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Detalle de productos vendidos
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3 text-right">Pedidos</th>
                        <th class="px-4 py-3 text-right">Unidades</th>
                        <th class="px-4 py-3 text-right">Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['products'] ?? [] as $index => $product)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-3">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $product['name'] }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $product['sku'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ $product['orders_count'] }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format($product['units_sold']) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                Q {{ number_format($product['revenue'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No hay datos disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

</x-filament-panels::page>