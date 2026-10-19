<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Collection;

class PricingService
{
    private ?Collection $cachedPromotions = null;
    public static function cents(string|int|float $amount): int
    {
        return (int) round((float) $amount * 100, 0, PHP_ROUND_HALF_UP);
    }

    public function activePromotions(bool $lock = false): Collection
    {
        if (!$lock && $this->cachedPromotions !== null) {
            return $this->cachedPromotions;
        }
        $at = now();
        $query = Promotion::query()->where('is_active', true)->where('type', 'category_bundle')
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $at))
            ->orderBy('id');
        $promotions = ($lock ? $query->lockForUpdate() : $query)->get();
        if (!$lock) {
            $this->cachedPromotions = $promotions;
        }
        return $promotions;
    }

    public function quote(Collection $items, bool $lock = false): Collection
    {
        if ($items->isEmpty()) {
            return $items;
        }
        $promotions = $this->activePromotions($lock);
        $result = $items->map(function ($item) {
            $item['base_subtotal_cents'] = self::cents($item['product']->price) * $item['quantity'];
            $item['promotion_discount_cents'] = 0;
            $item['promotion_snapshot'] = null;
            return $item;
        });
        foreach ($result->groupBy(fn ($item) => $item['product']->category_id . ':' . self::cents($item['product']->price)) as $group) {
            $first = $group->first();
            $price = self::cents($first['product']->price);
            $quantities = $group->mapWithKeys(fn ($item) => [$item['product']->id => $item['quantity']])->all();
            $best = null;
            foreach ($promotions as $promotion) {
                if ((int) $promotion->category_id !== (int) $first['product']->category_id || self::cents($promotion->eligible_price) !== $price) {
                    continue;
                }
                $candidate = app(BundleCalculator::class)->allocate($quantities, $price, $promotion->bundle_quantity, self::cents($promotion->bundle_price));
                if ($candidate['discount'] > ($best['discount'] ?? 0)) {
                    $best = $candidate + ['promotion' => $promotion];
                }
            }
            if (!$best) {
                continue;
            }
            $promotion = $best['promotion'];
            $result = $result->map(function ($item) use ($best, $promotion) {
                $id = $item['product']->id;
                if (isset($best['allocation'][$id])) {
                    $item['promotion_discount_cents'] = $best['allocation'][$id];
                    $item['promotion_snapshot'] = ['id' => $promotion->id, 'name' => $promotion->name, 'type' => $promotion->type, 'category_id' => $promotion->category_id, 'eligible_price' => (string) $promotion->eligible_price, 'bundle_quantity' => $promotion->bundle_quantity, 'bundle_price' => (string) $promotion->bundle_price, 'group_bundles' => $best['bundles']];
                }
                return $item;
            });
        }
        return $result->map(function ($item) {
            $item['subtotal'] = ($item['base_subtotal_cents'] - $item['promotion_discount_cents']) / 100;
            return $item;
        });
    }

    public function fingerprint(Collection $items): string
    {
        return hash('sha256', json_encode($items->sortBy('product.id')->map(fn ($item) => [
            $item['product']->id, $item['quantity'], $item['base_subtotal_cents'], $item['promotion_discount_cents'], $item['promotion_snapshot'],
        ])->values()->all(), JSON_THROW_ON_ERROR));
    }
}
