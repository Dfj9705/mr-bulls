<?php

namespace App\Filament\Pages;

use App\Services\Reports\TopProductsReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Exports\TopProductsReportExport;
use App\Services\Reports\TopProductsPdfReportService;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

class TopProductsReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-trophy';

    protected static ?string $navigationLabel =
        'Productos más vendidos';

    protected static ?string $navigationGroup =
        'Reportes';

    protected static ?int $navigationSort = 2;

    protected static ?string $title =
        'Productos más vendidos';

    protected static ?string $slug =
        'reportes/productos';

    protected static string $view =
        'filament.pages.top-products-report';

    public ?array $data = [];

    public array $report = [];

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'sort_by' => 'quantity',
            'limit' => '10',
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

                Select::make('sort_by')
                    ->label('Ordenar por')
                    ->options([
                        'quantity' => 'Unidades vendidas',
                        'revenue' => 'Ingresos generados',
                    ])
                    ->required(),

                Select::make('limit')
                    ->label('Mostrar productos')
                    ->options([
                        '5' => 'Top 5',
                        '10' => 'Top 10',
                        '20' => 'Top 20',
                        'all' => 'Todos',
                    ])
                    ->required(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function loadReport(): void
    {
        $filters = $this->form->getState();

        $limit = ($filters['limit'] ?? '10') === 'all'
            ? null
            : (int) $filters['limit'];

        $this->report = app(TopProductsReportService::class)
            ->getReport(
                $filters['date_from'],
                $filters['date_to'],
                $filters['sort_by'],
                $limit
            );
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

                    $limit = $filters['limit'] === 'all'
                        ? null
                        : (int) $filters['limit'];

                    return Excel::download(
                        new TopProductsReportExport(
                            $filters['date_from'],
                            $filters['date_to'],
                            $filters['sort_by'],
                            $limit
                        ),
                        'productos-mas-vendidos-' .
                        now()->format('Y-m-d_His') . '.xlsx'
                    );
                }),

            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(function () {
                    $filters = $this->form->getState();

                    $limit = $filters['limit'] === 'all'
                        ? null
                        : (int) $filters['limit'];

                    $pdf = app(TopProductsPdfReportService::class)
                        ->generate(
                            $filters['date_from'],
                            $filters['date_to'],
                            $filters['sort_by'],
                            $limit
                        );

                    return response()->streamDownload(
                        fn() => print ($pdf),
                        'productos-mas-vendidos-' .
                        now()->format('Y-m-d_His') . '.pdf',
                        [
                            'Content-Type' => 'application/pdf',
                        ]
                    );
                }),
        ];
    }

}

