<?php

namespace App\Services\Reports;

use App\Models\Product;
use App\Models\InventoryMovement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class InventoryReportService
{
    /**
     * Consulta del inventario actual.
     */
    public function inventoryQuery(
        ?string $search = null,
        string $status = 'all'
    ): Builder {
        if (
            !in_array($status, [
                'all',
                'available',
                'low',
                'out',
            ], true)
        ) {
            throw new InvalidArgumentException(
                'Estado de inventario no válido.'
            );
        }

        $query = Product::query()
            ->with('category');

        if (filled($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        switch ($status) {
            case 'available':
                $query->whereColumn('stock', '>', 'minimum_stock');
                break;

            case 'low':
                $query->where('stock', '>', 0)
                    ->whereColumn('stock', '<=', 'minimum_stock');
                break;

            case 'out':
                $query->where('stock', 0);
                break;
        }

        return $query->orderBy('name');
    }

    /**
     * Resumen general del inventario.
     */
    public function summary(): array
    {
        return [
            'total_products' => Product::count(),

            'total_units' => (int) Product::sum('stock'),

            'available_products' => Product::query()
                ->whereColumn('stock', '>', 'minimum_stock')
                ->count(),

            'low_stock_products' => Product::query()
                ->where('stock', '>', 0)
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->count(),

            'out_of_stock_products' => Product::query()
                ->where('stock', 0)
                ->count(),

            'inventory_value' => (float) Product::query()
                ->selectRaw('COALESCE(SUM(stock * price), 0) AS value')
                ->value('value'),
        ];
    }

    /**
     * Consulta histórica de movimientos.
     */
    public function movementsQuery(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $type = null,
        ?int $productId = null
    ): Builder {
        $from = $dateFrom
            ? Carbon::parse($dateFrom)->startOfDay()
            : now()->startOfMonth();

        $to = $dateTo
            ? Carbon::parse($dateTo)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            throw new InvalidArgumentException(
                'La fecha inicial no puede ser posterior a la final.'
            );
        }

        return InventoryMovement::query()
            ->with(['product', 'order', 'user'])
            ->whereBetween('created_at', [$from, $to])
            ->when(
                filled($type),
                fn(Builder $query) =>
                    $query->where('type', $type)
            )
            ->when(
                $productId !== null,
                fn(Builder $query) =>
                    $query->where('product_id', $productId)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Resumen de movimientos en un período.
     */
    public function movementsSummary(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $type = null,
        ?int $productId = null
    ): array {
        $query = $this->movementsQuery(
            $dateFrom,
            $dateTo,
            $type,
            $productId
        );

        return [
            'total_movements' => (clone $query)->count(),

            'total_quantity' => (int) (clone $query)
                ->sum('quantity'),
        ];
    }
}
