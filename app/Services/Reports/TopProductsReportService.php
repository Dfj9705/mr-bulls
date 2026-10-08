<?php

namespace App\Services\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TopProductsReportService
{
    public function getReport(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        string $sortBy = 'quantity',
        ?int $limit = null
    ): array {
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

        if (!in_array($sortBy, ['quantity', 'revenue'], true)) {
            throw new InvalidArgumentException(
                'El criterio de ordenamiento no es válido.'
            );
        }

        if ($limit !== null && $limit < 1) {
            throw new InvalidArgumentException(
                'El límite debe ser mayor que cero.'
            );
        }

        $baseQuery = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('orders.paid_at', [$from, $to]);

        // Agrupamos por producto y SKU histórico.
        $groupedQuery = (clone $baseQuery)
            ->select([
                'order_items.product_id',
                'order_items.product_name',
                'order_items.product_sku',
            ])
            ->selectRaw('SUM(order_items.quantity) AS units_sold')
            ->selectRaw('SUM(order_items.subtotal) AS revenue')
            ->selectRaw(
                'COUNT(DISTINCT orders.id) AS orders_count'
            )
            ->groupBy(
                'order_items.product_id',
                'order_items.product_name',
                'order_items.product_sku'
            );

        $sortColumn = $sortBy === 'revenue'
            ? 'revenue'
            : 'units_sold';

        $groupedQuery
            ->orderByDesc($sortColumn)
            ->orderBy('order_items.product_name');

        // Resumen completo, sin aplicar el límite del ranking.
        $summary = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) AS units')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) AS revenue')
            ->first();

        $productsCount = DB::query()
            ->fromSub(clone $groupedQuery, 'products_report')
            ->count();

        if ($limit !== null) {
            $groupedQuery->limit($limit);
        }

        $products = $groupedQuery
            ->get()
            ->map(fn($item) => [
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->product_sku,
                'units_sold' => (int) $item->units_sold,
                'revenue' => (float) $item->revenue,
                'orders_count' => (int) $item->orders_count,
            ])
            ->all();

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'sort_by' => $sortBy,
            'total_units' => (int) ($summary->units ?? 0),
            'total_revenue' => (float) ($summary->revenue ?? 0),
            'total_products' => $productsCount,
            'products' => $products,
        ];
    }
}
