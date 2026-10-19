<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletReversalApplication extends Model
{
    protected $fillable = [
        'wallet_transaction_id',
        'wallet_reversal_id',
        'amount',
        'reversed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reversed_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'wallet_transaction_id'
        );
    }

    public function reversal(): BelongsTo
    {
        return $this->belongsTo(
            WalletReversal::class,
            'wallet_reversal_id'
        );
    }
}
