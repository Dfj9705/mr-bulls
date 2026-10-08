<?php

namespace App\Exports;

use App\Services\Reports\TopProductsReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopProductsReportExport implements FromArray, WithStyles, ShouldAutoSize
{
    private array $report;

    public function __construct(
        string $dateFrom,
        string $dateTo,
        string $sortBy = 'quantity',
        ?int $limit = null
    ) {
        $this->report = app(TopProductsReportService::class)
            ->getReport($dateFrom, $dateTo, $sortBy, $limit);
    }

    public function array(): array
    {
        $report = $this->report;

        $rows = [
            ['MR BULLS - PRODUCTOS MÁS VENDIDOS'],
            ['Período', $report['date_from'] . ' al ' . $report['date_to']],
            [
                'Ordenamiento',
                $report['sort_by'] === 'revenue'
                ? 'Ingresos generados'
                : 'Unidades vendidas'
            ],
            [],
            ['RESUMEN GENERAL'],
            ['Unidades vendidas', $report['total_units']],
            ['Ingresos por productos', $report['total_revenue']],
            ['Productos diferentes', $report['total_products']],
            [],
            ['RANKING DE PRODUCTOS'],
            ['#', 'Producto', 'SKU', 'Pedidos', 'Unidades', 'Ingresos (Q)'],
        ];

        foreach ($report['products'] as $index => $product) {
            $rows[] = [
                $index + 1,
                $product['name'],
                $product['sku'] ?? '',
                $product['orders_count'],
                $product['units_sold'],
                $product['revenue'],
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        foreach ([1, 5, 9, 11] as $row) {
            $sheet->getStyle("A{$row}:F{$row}")
                ->getFont()
                ->setBold(true);
        }

        $sheet->getStyle('B7')
            ->getNumberFormat()
            ->setFormatCode('"Q" #,##0.00');

        $lastRow = max(11, $sheet->getHighestRow());

        $sheet->getStyle("F11:F{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('"Q" #,##0.00');

        $sheet->freezePane('A11');

        $sheet->setAutoFilter("A9:F{$lastRow}");

        return [];
    }
}
