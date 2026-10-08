<?php

namespace App\Exports;

use App\Models\Order;
use App\Services\Reports\OrderReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    public function __construct(
        protected array $filters = []
    ) {
    }

    public function query(): Builder
    {
        return OrderReportQuery::build($this->filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
    public function headings(): array
    {
        return [
            'No. Pedido',
            'Fecha',
            'Cliente',
            'Correo',
            'Teléfono',
            'Departamento',
            'Municipio',
            'Dirección',
            'Subtotal (Q)',
            'Envío (Q)',
            'Total (Q)',
            'Estado',
            'Estado de pago',
            'Método de pago',
            'Link de Pago'
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('d/m/Y H:i'),
            $order->customer_name,
            $order->customer_email,
            $order->customer_phone,
            $order->shipping_department,
            $order->shipping_municipality,
            $order->shipping_address,
            (float) $order->subtotal,
            (float) $order->shipping_cost,
            (float) $order->total,
            match ($order->status) {
                'pending' => 'Pendiente',
                'processing' => 'Procesando',
                'shipped' => 'Enviado',
                'completed' => 'Completado',
                'cancelled' => 'Cancelado',
                default => $order->status,
            },
            match ($order->payment_status) {
                'pending' => 'Pendiente',
                'paid' => 'Pagado',
                'failed' => 'Fallido',
                'cancelled' => 'Cancelado',
                default => $order->payment_status,
            },
            $order->payment_method,
            $order->payment_url,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:O1')
            ->getFont()
            ->setBold(true);

        $sheet->getStyle('I:K')
            ->getNumberFormat()
            ->setFormatCode('"Q" #,##0.00');

        $sheet->freezePane('A2');

        $sheet->setAutoFilter(
            'A1:O' . max(1, $sheet->getHighestRow())
        );

        return [];
    }
}
