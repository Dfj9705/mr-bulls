<?php

namespace App\Services\Reports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OrderReportQuery
{
    public static function build(array $filters = []): Builder
    {
        return Order::query()
            ->when(
                $filters['date_from'] ?? null,
                fn(Builder $query, $date) =>
                    $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn(Builder $query, $date) =>
                    $query->whereDate('created_at', '<=', $date)
            )
            ->when(
                $filters['status'] ?? null,
                fn(Builder $query, $status) =>
                    $query->where('status', $status)
            )
            ->when(
                $filters['payment_status'] ?? null,
                fn(Builder $query, $status) =>
                    $query->where('payment_status', $status)
            );
    }
}
