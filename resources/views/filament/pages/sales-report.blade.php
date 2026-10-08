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

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        @foreach ([
                [
                    'label' => 'Ventas cobradas',
                    'value' => 'Q ' . number_format($summary['total_sales'] ?? 0, 2),
                ],
                [
                    'label' => 'Pedidos pagados',
                    'value' => $summary['paid_orders'] ?? 0,
                ],
                [
                    'label' => 'Ticket promedio',
                    'value' => 'Q ' . number_format($summary['average_ticket'] ?? 0, 2),
                ],
                [
                    'label' => 'Pendiente de cobro',
                    'value' => 'Q ' . number_format($summary['pending_amount'] ?? 0, 2),
                ],
            ] as $metric)

            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $metric['label'] }}
                </div>

                <div class="mt-2 text-2xl font-bold
                                text-gray-950 dark:text-white">
                    {{ $metric['value'] }}
                </div>
            </x-filament::section>

        @endforeach

    </div>

    @livewire(\App\Filament\Widgets\SalesChart::class, [
        'dateFrom' => $summary['date_from'] ?? null,
        'dateTo' => $summary['date_to'] ?? null,
    ])

    <x-filament::section>
        <x-slot name="heading">
            Ventas diarias
        </x-slot>

        @if (count($dailySales))
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3 text-right">
                                Pedidos pagados
                            </th>
                            <th class="px-4 py-3 text-right">
                                Total cobrado
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailySales as $day)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3">
                                    {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ $day['orders'] }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    Q {{ number_format($day['total'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-gray-500">
                No hay ventas cobradas en el período seleccionado.
            </div>
        @endif
    </x-filament::section>

    @if (($summary['historical_paid_without_date'] ?? 0) > 0)
        <div class="text-sm text-warning-600 dark:text-warning-400">
            Existen {{ $summary['historical_paid_without_date'] }}
            pedidos pagados sin fecha de pago registrada.
            No se incluyen en las ventas por período.
        </div>
    @endif

</x-filament-panels::page>