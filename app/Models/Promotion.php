<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Promotion extends Model
{
    protected $fillable = ['name', 'type', 'category_id', 'eligible_price', 'bundle_quantity', 'bundle_price', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = ['eligible_price' => 'decimal:2', 'bundle_price' => 'decimal:2', 'bundle_quantity' => 'integer', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Promotion $promotion) {
            if ($promotion->type !== 'category_bundle' || $promotion->bundle_quantity < 2 ||
                (float) $promotion->eligible_price <= 0 || (float) $promotion->bundle_price < 0 ||
                (float) $promotion->bundle_price >= round((float) $promotion->eligible_price * $promotion->bundle_quantity, 2)) {
                throw ValidationException::withMessages(['bundle_price' => 'El paquete debe costar menos que sus unidades a precio normal.']);
            }
            if ($promotion->starts_at && $promotion->ends_at && $promotion->ends_at <= $promotion->starts_at) {
                throw ValidationException::withMessages(['ends_at' => 'El fin debe ser posterior al inicio.']);
            }
        });
    }
}
