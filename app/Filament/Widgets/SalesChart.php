<?php

namespace App\Filament\Widgets;

use App\Services\Reports\SalesReportService;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Evolución de ventas';

    protected static ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    protected $listeners = [
        'salesReportUpdated' => 'updateFilters',
    ];

    public function updateFilters(
        string $dateFrom,
        string $dateTo
    ): void {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;

        $this->updateChartData();
    }

    protected function getData(): array
    {
        $sales = app(SalesReportService::class)
            ->dailySales(
                $this->dateFrom,
                $this->dateTo
            );

        return [
            'datasets' => [
                [
                    'label' => 'Ventas cobradas (Q)',
                    'data' => array_column($sales, 'total'),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22,163,74,0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => array_map(
                fn($day) => date('d/m', strtotime($day['date'])),
                $sales
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
