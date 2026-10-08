<?php

namespace App\Services\Reports;

use Mpdf\Mpdf;

class SalesPdfReportService
{
    public function generate(
        string $dateFrom,
        string $dateTo
    ): string {
        $service = app(SalesReportService::class);

        $summary = $service->summary($dateFrom, $dateTo);
        $dailySales = $service->dailySales($dateFrom, $dateTo);

        $html = view('reports.sales', [
            'summary' => $summary,
            'dailySales' => $dailySales,
            'generatedAt' => now(),
        ])->render();

        $tempDir = storage_path('app/mpdf-temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'margin_top' => 18,
            'margin_bottom' => 18,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);

        $mpdf->SetTitle('Reporte de ventas - Mr Bulls');

        $mpdf->SetHTMLFooter(
            '<div style="text-align:right;font-size:9px;color:#666">
                Página {PAGENO} de {nbpg}
            </div>'
        );

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}
