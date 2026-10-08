<?php

namespace App\Exports;

use App\Services\Reports\SalesReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromArray, WithStyles, ShouldAutoSize
{
    private array $summary;
    private array $dailySales;

    public function __construct(
        string $dateFrom,
        string $dateTo
    ) {
        $service = app(SalesReportService::class);

        $this->summary = $service->summary($dateFrom, $dateTo);
        $this->dailySales = $service->dailySales($dateFrom, $dateTo);
    }

    public function array(): array
    {
        $rows = [
            ['MR BULLS - REPORTE DE VENTAS'],
            [
                'Período',
                $this->summary['date_from'] . ' al ' .
                $this->summary['date_to'],
            ],
            [],
            ['RESUMEN GENERAL'],
            ['Ventas cobradas', $this->summary['total_sales']],
            ['Pedidos pagados', $this->summary['paid_orders']],
            ['Pedidos creados', $this->summary['total_orders']],
            ['Ticket promedio', $this->summary['average_ticket']],
            ['Pendiente de cobro', $this->summary['pending_amount']],
            [],
            ['DETALLE DE VENTAS DIARIAS'],
            ['Fecha', 'Pedidos pagados', 'Total cobrado'],
        ];

        foreach ($this->dailySales as $day) {
            $rows[] = [
                $day['date'],
                $day['orders'],
                $day['total'],
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        foreach ([1, 4, 11, 12] as $row) {
            $sheet->getStyle("A{$row}:C{$row}")
                ->getFont()
                ->setBold(true);
        }

        $sheet->getStyle('B5:B9')
            ->getNumberFormat()
            ->setFormatCode('"Q" #,##0.00');

        // Los conteos no son importes monetarios.
        $sheet->getStyle('B6:B7')
            ->getNumberFormat()
            ->setFormatCode('0');

        $sheet->getStyle('C13:C' . max(13, $sheet->getHighestRow()))
            ->getNumberFormat()
            ->setFormatCode('"Q" #,##0.00');

        $sheet->freezePane('A13');

        return [];
    }
}
