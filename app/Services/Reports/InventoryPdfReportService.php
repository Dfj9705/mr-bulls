<?php

namespace App\Services\Reports;

use Mpdf\Mpdf;

class InventoryPdfReportService
{
    public function stock(
        ?string $search = null,
        string $status = 'all'
    ): string {
        $service = app(InventoryReportService::class);

        $html = view('reports.inventory-stock', [
            'summary' => $service->summary(),
            'products' => $service
                ->inventoryQuery($search, $status)
                ->get(),
            'search' => $search,
            'status' => $status,
            'generatedAt' => now(),
        ])->render();

        return $this->renderPdf($html, 'Existencias - Mr Bulls');
    }

    public function movements(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $type = null,
        ?int $productId = null
    ): string {
        $service = app(InventoryReportService::class);

        $html = view('reports.inventory-movements', [
            'movements' => $service
                ->movementsQuery($dateFrom, $dateTo, $type, $productId)
                ->get(),
            'summary' => $service
                ->movementsSummary($dateFrom, $dateTo, $type, $productId),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'type' => $type,
            'productId' => $productId,
            'generatedAt' => now(),
        ])->render();

        return $this->renderPdf($html, 'Movimientos - Mr Bulls');
    }

    private function renderPdf(string $html, string $title): string
    {
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
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->SetTitle($title);

        $mpdf->SetHTMLFooter(
            '<div style="text-align:right;font-size:9px">
                Página {PAGENO} de {nbpg}
            </div>'
        );

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}
