<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Exports\OrdersExport;
use App\Filament\Resources\OrderResource;
use App\Services\Reports\OrderReportService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new OrdersExport(),
                        'pedidos-' . now()->format('Y-m-d_His') . '.xlsx'
                    );
                }),


            Actions\Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(function (OrderReportService $reportService): StreamedResponse {
                    $pdf = $reportService->generatePdf();

                    $filename = 'pedidos-' .
                        now()->format('Y-m-d_His') . '.pdf';

                    return response()->streamDownload(
                        fn() => print ($pdf),
                        $filename,
                        [
                            'Content-Type' => 'application/pdf',
                        ]
                    );
                }),

        ];
    }

}
