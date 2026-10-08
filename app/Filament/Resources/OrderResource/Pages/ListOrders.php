<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Exports\OrdersExport;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\Reports\OrderReportService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function reportForm(): array
    {
        return [
            DatePicker::make('date_from')
                ->label('Fecha inicial')
                ->native(false)
                ->maxDate(now()),

            DatePicker::make('date_to')
                ->label('Fecha final')
                ->native(false)
                ->maxDate(now())
                ->afterOrEqual('date_from'),

            Select::make('status')
                ->label('Estado del pedido')
                ->placeholder('Todos')
                ->options([
                    Order::STATUS_PENDING => 'Pendiente',
                    Order::STATUS_PROCESSING => 'Procesando',
                    Order::STATUS_SHIPPED => 'Enviado',
                    Order::STATUS_COMPLETED => 'Completado',
                    Order::STATUS_CANCELLED => 'Cancelado',
                ]),

            Select::make('payment_status')
                ->label('Estado del pago')
                ->placeholder('Todos')
                ->options([
                    Order::PAYMENT_PENDING => 'Pendiente',
                    Order::PAYMENT_PAID => 'Pagado',
                    Order::PAYMENT_FAILED => 'Fallido',
                    Order::PAYMENT_CANCELLED => 'Cancelado',
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form($this->reportForm())
                ->modalHeading('Exportar pedidos a Excel')
                ->modalSubmitActionLabel('Descargar Excel')
                ->action(function (array $data) {
                    return Excel::download(
                        new OrdersExport($data),
                        'pedidos-' .
                        now()->format('Y-m-d_His') .
                        '.xlsx'
                    );
                }),

            Actions\Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->form($this->reportForm())
                ->modalHeading('Exportar pedidos a PDF')
                ->modalSubmitActionLabel('Descargar PDF')
                ->action(function (array $data): StreamedResponse {
                    $pdf = app(OrderReportService::class)
                        ->generatePdf($data);

                    $filename = 'pedidos-' .
                        now()->format('Y-m-d_His') . '.pdf';

                    return response()->streamDownload(
                        fn() => print ($pdf),
                        $filename,
                        ['Content-Type' => 'application/pdf']
                    );
                }),
        ];
    }
}
