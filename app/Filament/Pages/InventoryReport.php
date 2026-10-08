<?php

namespace App\Filament\Pages;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\Reports\InventoryReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use App\Exports\InventoryStockExport;
use App\Exports\InventoryMovementsExport;
use App\Services\Reports\InventoryPdfReportService;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-archive-box';

    protected static ?string $navigationLabel =
        'Reporte de inventario';

    protected static ?string $navigationGroup =
        'Reportes';

    protected static ?int $navigationSort = 3;

    protected static ?string $title =
        'Reporte de inventario';

    protected static ?string $slug =
        'reportes/inventario';

    protected static string $view =
        'filament.pages.inventory-report';

    public ?array $inventoryFilters = [];

    public ?array $movementFilters = [];

    public array $summary = [];

    public array $movementSummary = [];

    public array $products = [];

    public array $movements = [];

    public function mount(): void
    {
        $this->inventoryForm->fill([
            'search' => null,
            'status' => 'all',
        ]);

        $this->movementForm->fill([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'type' => null,
            'product_id' => null,
        ]);

        $this->loadInventory();
        $this->loadMovements();
    }

    protected function getForms(): array
    {
        return [
            'inventoryForm',
            'movementForm',
        ];
    }

    public function inventoryForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->label('Buscar producto')
                    ->placeholder('Nombre o SKU'),

                Select::make('status')
                    ->label('Estado del inventario')
                    ->options([
                        'all' => 'Todos',
                        'available' => 'Disponible',
                        'low' => 'Stock bajo',
                        'out' => 'Agotado',
                    ])
                    ->required(),
            ])
            ->columns(2)
            ->statePath('inventoryFilters');
    }

    public function movementForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')
                    ->label('Desde')
                    ->native(false)
                    ->required(),

                DatePicker::make('date_to')
                    ->label('Hasta')
                    ->native(false)
                    ->afterOrEqual('date_from')
                    ->required(),

                Select::make('type')
                    ->label('Tipo de movimiento')
                    ->options(
                        InventoryMovement::query()
                            ->whereNotNull('type')
                            ->distinct()
                            ->orderBy('type')
                            ->pluck('type', 'type')
                            ->all()
                    )
                    ->placeholder('Todos')
                    ->searchable(),

                Select::make('product_id')
                    ->label('Producto')
                    ->options(
                        Product::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()
                    )
                    ->placeholder('Todos')
                    ->searchable(),
            ])
            ->columns(2)
            ->statePath('movementFilters');
    }

    public function loadInventory(): void
    {
        $filters = $this->inventoryForm->getState();

        $service = app(InventoryReportService::class);

        $this->summary = $service->summary();

        $this->products = $service
            ->inventoryQuery(
                $filters['search'] ?? null,
                $filters['status'] ?? 'all'
            )
            ->get()
            ->map(fn(Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name ?? '-',
                'stock' => $product->stock,
                'minimum_stock' => $product->minimum_stock,
                'price' => (float) $product->price,
                'value' => $product->stock * (float) $product->price,
                'status' => $product->stock === 0
                    ? 'out'
                    : ($product->stock <= $product->minimum_stock
                        ? 'low'
                        : 'available'),
            ])
            ->all();
    }

    public function loadMovements(): void
    {
        $filters = $this->movementForm->getState();

        $service = app(InventoryReportService::class);

        $args = [
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null,
            $filters['type'] ?? null,
            !empty($filters['product_id'])
            ? (int) $filters['product_id']
            : null,
        ];

        $this->movementSummary = $service
            ->movementsSummary(...$args);

        // Límite inicial para evitar cargar un historial
        // excesivamente grande en Livewire.
        $this->movements = $service
            ->movementsQuery(...$args)
            ->limit(100)
            ->get()
            ->map(fn(InventoryMovement $movement) => [
                'date' => $movement->created_at?->format('d/m/Y H:i'),
                'product' => $movement->product?->name ?? 'Producto eliminado',
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'stock_before' => $movement->stock_before,
                'stock_after' => $movement->stock_after,
                'reason' => $movement->reason,
                'order' => $movement->order?->order_number ?? '-',
                'user' => $movement->user?->name ?? 'Sistema',
            ])
            ->all();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('reportes.ver') ?? false;
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('stockExcel')
                ->label('Existencias Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $filters = $this->inventoryForm->getState();

                    return Excel::download(
                        new InventoryStockExport(
                            $filters['search'] ?? null,
                            $filters['status'] ?? 'all'
                        ),
                        'inventario-existencias-' .
                        now()->format('Ymd-His') . '.xlsx'
                    );
                }),

            Action::make('stockPdf')
                ->label('Existencias PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(function () {
                    $filters = $this->inventoryForm->getState();

                    $pdf = app(InventoryPdfReportService::class)
                        ->stock(
                            $filters['search'] ?? null,
                            $filters['status'] ?? 'all'
                        );

                    return response()->streamDownload(
                        fn() => print ($pdf),
                        'inventario-existencias-' .
                        now()->format('Ymd-His') . '.pdf',
                        ['Content-Type' => 'application/pdf']
                    );
                }),

            Action::make('movementsExcel')
                ->label('Movimientos Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $filters = $this->movementForm->getState();

                    return Excel::download(
                        new InventoryMovementsExport(
                            $filters['date_from'],
                            $filters['date_to'],
                            $filters['type'] ?? null,
                            filled($filters['product_id'] ?? null)
                            ? (int) $filters['product_id']
                            : null
                        ),
                        'inventario-movimientos-' .
                        now()->format('Ymd-His') . '.xlsx'
                    );
                }),

            Action::make('movementsPdf')
                ->label('Movimientos PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(function () {
                    $filters = $this->movementForm->getState();

                    $pdf = app(InventoryPdfReportService::class)
                        ->movements(
                            $filters['date_from'],
                            $filters['date_to'],
                            $filters['type'] ?? null,
                            filled($filters['product_id'] ?? null)
                            ? (int) $filters['product_id']
                            : null
                        );

                    return response()->streamDownload(
                        fn() => print ($pdf),
                        'inventario-movimientos-' .
                        now()->format('Ymd-His') . '.pdf',
                        ['Content-Type' => 'application/pdf']
                    );
                }),
        ];
    }

}
