<?php

namespace App\Exports;

use App\Services\Reports\InventoryReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryMovementsExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
        protected ?string $type = null,
        protected ?int $productId = null
    ) {
    }

    public function array(): array
    {
        $service = app(InventoryReportService::class);

        $summary = $service->movementsSummary(
            $this->dateFrom,
            $this->dateTo,
            $this->type,
            $this->productId
        );

        $rows = [
            ['MR BULLS - MOVIMIENTOS DE INVENTARIO'],
            ['Desde', $this->dateFrom],
            ['Hasta', $this->dateTo],
            ['Tipo', $this->type ?: 'Todos'],
            ['Producto ID', $this->productId ?: 'Todos'],
            [],
            ['Total de movimientos', $summary['total_movements']],
            ['Cantidad acumulada (sin signo)', $summary['total_quantity']],
            [],
            ['Fecha', 'Producto', 'SKU', 'Tipo', 'Cantidad', 'Antes', 'Después', 'Pedido', 'Usuario', 'Motivo'],
        ];

        foreach (
            $service->movementsQuery(
                $this->dateFrom,
                $this->dateTo,
                $this->type,
                $this->productId
            )->lazy(500) as $movement
        ) {
            $rows[] = [
                $movement->created_at?->format('d/m/Y H:i'),
                $movement->product?->name ?? 'Producto eliminado',
                $movement->product?->sku ?? '-',
                $movement->type,
                $movement->quantity,
                $movement->stock_before,
                $movement->stock_after,
                $movement->order?->order_number ?? '-',
                $movement->user?->name ?? 'Sistema',
                $movement->reason,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        foreach ([1, 8] as $row) {
            $sheet->getStyle("A{$row}:J{$row}")
                ->getFont()->setBold(true);
        }


        return [];
    }
}
