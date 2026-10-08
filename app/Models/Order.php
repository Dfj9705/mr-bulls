<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Mail\PaymentLinkAvailable;
use Illuminate\Support\Facades\Mail;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'order_number',

        'customer_name',
        'customer_email',
        'customer_phone',

        'shipping_department',
        'shipping_municipality',
        'shipping_address',
        'shipping_references',

        'subtotal',
        'shipping_cost',
        'total',

        'status',
        'payment_status',
        'payment_method',
        'payment_url',
        'paid_at',

        'customer_notes',
        'admin_notes',

        'shipping_recipient',
        'shipping_phone',

        'public_token',

        'stock_restored_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'stock_restored_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted(): void
    {
        static::updated(function (Order $order) {

            /*
             * Solo notificamos cuando payment_url
             * realmente cambió.
             */
            if (!$order->wasChanged('payment_url')) {
                return;
            }

            /*
             * No enviamos nada si el link fue eliminado.
             */
            if (blank($order->payment_url)) {
                return;
            }

            /*
             * Si ya está pagado, tampoco tiene sentido
             * enviar un nuevo link.
             */
            if ($order->payment_status === self::PAYMENT_PAID) {
                return;
            }

            Mail::to($order->customer_email)
                ->queue(
                    new PaymentLinkAvailable($order)
                );
        });


        static::updating(function (Order $order) {
            if (!$order->isDirty('payment_status')) {
                return;
            }

            if ($order->payment_status === self::PAYMENT_PAID) {
                // Conserva la fecha original si ya existía.
                $order->paid_at ??= now();
            }
        });

    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)
            ->orderBy('created_at');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}