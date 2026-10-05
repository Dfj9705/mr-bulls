<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'department',
        'municipality',
        'address',
        'references',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (Address $address) {
            if (!$address->is_default) {
                return;
            }

            static::query()
                ->where('user_id', $address->user_id)
                ->whereKeyNot($address->id)
                ->where('is_default', true)
                ->update([
                    'is_default' => false,
                ]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}