<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletReversal extends Model
{
    protected $fillable = [
        'wallet_id',
        'order_id',
        'original_amount',
        'pending_amount',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'pending_amount' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(
            WalletReversalApplication::class
        );
    }
}
