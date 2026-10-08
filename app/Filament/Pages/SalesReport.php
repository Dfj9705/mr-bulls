<?php

namespace App\Filament\Pages;

use App\Services\Reports\SalesReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Filament\Widgets\SalesChart;
use App\Exports\SalesReportExport;
use App\Services\Reports\SalesPdfReportService;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
class SalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-chart-bar';

    protected static ?string $navigationLabel =
        'Reporte de ventas';

    protected static ?string $navigationGroup =
        'Reportes';

    protected static ?int $navigationSort = 1;

    protected static ?string $title =
        'Reporte de ventas';

    protected static ?string $slug =
        'reportes/ventas';

    protected static string $view =
        'filament.pages.sales-report';

    public ?array $data = [];

    public array $summary = [];

    public array $dailySales = [];

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()
                ->startOfMonth()
                ->toDateString(),

            'date_to' => now()->toDateString(),
        ]);

        $this->loadReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')
                    ->label('Fecha inicial')
                    ->required()
                    ->native(false)
                    ->maxDate(now()),

                DatePicker::make('date_to')
                    ->label('Fecha final')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('date_from')
                    ->maxDate(now()),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function loadReport(): void
    {
        $filters = $this->form->getState();

        $service = app(SalesReportService::class);

        $this->summary = $service->summary(
            $filters['date_from'],
            $filters['date_to']
        );

        $this->dailySales = $service->dailySales(
            $filters['date_from'],
            $filters['date_to']
        );

        $this->dispatch(
            'salesReportUpdated',
            dateFrom: $filters['date_from'],
            dateTo: $filters['date_to']
        )->to(SalesChart::class);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('reportes.ver') ?? false;
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $filters = $this->form->getState();

                    return Excel::download(
                        new SalesReportExport(
                            $filters['date_from'],
                            $filters['date_to']
                        ),
                        'ventas-' . now()->format('Y-m-d_His') . '.xlsx'
                    );
                }),

            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(function (): StreamedResponse {
                    $filters = $this->form->getState();

                    $pdf = app(SalesPdfReportService::class)
                        ->generate(
                            $filters['date_from'],
                            $filters['date_to']
                        );

                    return response()->streamDownload(
                        fn() => print ($pdf),
                        'ventas-' . now()->format('Y-m-d_His') . '.pdf',
                        ['Content-Type' => 'application/pdf']
                    );
                }),
        ];
    }

}
