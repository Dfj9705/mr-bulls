<?php

namespace App\Exports;

use App\Services\Reports\InventoryReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryStockExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected ?string $search = null,
        protected string $status = 'all'
    ) {
    }

    public function array(): array
    {
        $service = app(InventoryReportService::class);
        $summary = $service->summary();

        $rows = [
            ['MR BULLS - REPORTE DE INVENTARIO'],
            ['Generado', now()->format('d/m/Y H:i')],
            ['Filtro de estado', $this->status],
            ['Búsqueda', $this->search ?: 'Todos'],
            [],
            ['RESUMEN GENERAL'],
            ['Productos registrados', $summary['total_products']],
            ['Unidades disponibles', $summary['total_units']],
            ['Productos con stock bajo', $summary['low_stock_products']],
            ['Productos agotados', $summary['out_of_stock_products']],
            ['Valor potencial de venta', $summary['inventory_value']],
            [],
            ['EXISTENCIAS ACTUALES'],
            ['Producto', 'SKU', 'Categoría', 'Stock', 'Mínimo', 'Precio (Q)', 'Valor (Q)', 'Estado'],
        ];

        foreach ($service->inventoryQuery($this->search, $this->status)->cursor() as $product) {
            $status = $product->stock === 0
                ? 'Agotado'
                : ($product->stock <= $product->minimum_stock
                    ? 'Stock bajo'
                    : 'Disponible');

            $rows[] = [
                $product->name,
                $product->sku,
                $product->category?->name ?? '-',
                $product->stock,
                $product->minimum_stock,
                (float) $product->price,
                $product->stock * (float) $product->price,
                $status,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        foreach ([1, 12] as $row) {
            $sheet->getStyle("A{$row}:H{$row}")
                ->getFont()->setBold(true);
        }

        $sheet->getStyle('B11')
            ->getNumberFormat()
            ->setFormatCode('"Q" #,##0.00');

        $lastRow = $sheet->getHighestRow();

        if ($lastRow >= 15) {
            $sheet->getStyle("F15:G{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('"Q" #,##0.00');
        }


        return [];
    }
}
