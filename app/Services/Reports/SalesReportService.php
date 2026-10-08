<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function summary(
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $from = $dateFrom
            ? Carbon::parse($dateFrom)->startOfDay()
            : now()->startOfMonth();

        $to = $dateTo
            ? Carbon::parse($dateTo)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            throw new \InvalidArgumentException(
                'La fecha inicial no puede ser posterior a la final.'
            );
        }

        // Ventas cobradas durante el período.
        $paidOrders = Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to]);

        $totalSales = (float) (clone $paidOrders)->sum('total');
        $paidCount = (clone $paidOrders)->count();

        // Pedidos creados durante el período.
        $createdOrders = Order::query()
            ->whereBetween('created_at', [$from, $to]);

        $totalOrders = (clone $createdOrders)->count();

        // Pendientes de cobro, agrupados por fecha de creación.
        $pendingAmount = (float) Order::query()
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('created_at', [$from, $to])
            ->sum('total');

        // Pedidos pagados sin fecha histórica de pago.
        $historicalPaidCount = Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereNull('paid_at')
            ->count();

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'total_sales' => $totalSales,
            'paid_orders' => $paidCount,
            'total_orders' => $totalOrders,
            'pending_amount' => $pendingAmount,
            'average_ticket' => $paidCount > 0
                ? round($totalSales / $paidCount, 2)
                : 0,
            'historical_paid_without_date' => $historicalPaidCount,
        ];
    }

    public function dailySales(
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $from = $dateFrom
            ? Carbon::parse($dateFrom)->startOfDay()
            : now()->startOfMonth();

        $to = $dateTo
            ? Carbon::parse($dateTo)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            throw new \InvalidArgumentException(
                'La fecha inicial no puede ser posterior a la final.'
            );
        }

        return Order::query()
            ->selectRaw('DATE(paid_at) as sale_date')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as total_sales')
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->orderBy('sale_date')
            ->get()
            ->map(fn($row) => [
                'date' => $row->sale_date,
                'orders' => (int) $row->orders_count,
                'total' => (float) $row->total_sales,
            ])
            ->all();
    }
}
