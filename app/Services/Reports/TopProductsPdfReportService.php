<?php

namespace App\Services\Reports;

use Mpdf\Mpdf;

class TopProductsPdfReportService
{
    public function generate(
        string $dateFrom,
        string $dateTo,
        string $sortBy = 'quantity',
        ?int $limit = null
    ): string {
        $report = app(TopProductsReportService::class)
            ->getReport($dateFrom, $dateTo, $sortBy, $limit);

        $html = view('reports.top-products', [
            'report' => $report,
            'generatedAt' => now(),
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

        $mpdf->SetTitle('Productos más vendidos - Mr Bulls');

        $mpdf->SetHTMLFooter(
            '<div style="text-align:right;font-size:9px">
                Página {PAGENO} de {nbpg}
            </div>'
        );

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}
