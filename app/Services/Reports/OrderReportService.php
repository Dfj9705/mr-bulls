<?php

namespace App\Services\Reports;

use App\Models\Order;
use Mpdf\Mpdf;
use Illuminate\Database\Eloquent\Builder;

class OrderReportService
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


    public function generatePdf(array $filters = []): string
    {
        $orders = OrderReportQuery::build($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $html = view('reports.orders', [
            'orders' => $orders,
            'filters' => $filters,
            'generatedAt' => now(),
            'totalOrders' => $orders->count(),
            'totalAmount' => $orders
                ->where('payment_status', Order::PAYMENT_PAID)
                ->where('status', '!=', Order::STATUS_CANCELLED)
                ->sum('total'),
        ])->render();

        $tempDir = storage_path('app/mpdf-temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'tempDir' => $tempDir,
            'margin_top' => 18,
            'margin_bottom' => 18,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);

        $mpdf->SetTitle('Reporte de pedidos - Mr Bulls');

        $mpdf->SetHTMLFooter(
            '<div style="text-align:right;font-size:9px;">
            Página {PAGENO} de {nbpg}
        </div>'
        );

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

}
