<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class WalletService
{
    /**
     * Acredita el 2% de una compra pagada.
     *
     * El método es idempotente: no acredita dos veces
     * la recompensa del mismo pedido.
     */
    public function creditPurchase(Order $order): ?WalletTransaction
    {
        return DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->payment_status !== Order::PAYMENT_PAID) {
                return null;
            }

            if ($order->status === Order::STATUS_CANCELLED) {
                return null;
            }

            // Las compras de invitados no acumulan saldo.
            if (!$order->user_id) {
                return null;
            }

            $reference = 'purchase_reward:' . $order->id;

            $existing = WalletTransaction::query()
                ->where('reference', $reference)
                ->first();

            if ($existing) {
                return $existing;
            }

            $totalCents = $this->toCents($order->total);

            // 2% del importe efectivamente pagado.
            // El total debe excluir el saldo utilizado.
            $rewardCents = (int) round(
                $totalCents * 0.02,
                0,
                PHP_ROUND_HALF_UP
            );

            if ($rewardCents <= 0) {
                return null;
            }

            Wallet::query()->firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => '0.00']
            );

            $wallet = Wallet::query()
                ->where('user_id', $order->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $beforeCents = $this->toCents($wallet->balance);
            $afterCents = $beforeCents + $rewardCents;

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'order_id' => $order->id,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $this->toMoney($rewardCents),
                'balance_before' => $this->toMoney($beforeCents),
                'balance_after' => $this->toMoney($afterCents),
                'reference' => $reference,
                'description' => 'Recompensa del 2% por compra '
                    . $order->order_number,
            ]);

            $wallet->update([
                'balance' => $this->toMoney($afterCents),
            ]);

            return $transaction;
        }, 3);
    }

    private function toCents(string|int|float $amount): int
    {
        if (!is_numeric($amount)) {
            throw new RuntimeException('Importe monetario inválido.');
        }

        return (int) round(
            (float) $amount * 100,
            0,
            PHP_ROUND_HALF_UP
        );
    }

    private function toMoney(int $cents): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($cents, 100),
            $cents % 100
        );
    }


    /**
     * Reserva saldo del monedero para un pedido.
     *
     * Debe ejecutarse dentro de la misma transacción
     * de base de datos que crea el pedido.
     *
     * El importe se recibe en centavos.
     */
    public function reserveBalance(
        Order $order,
        int $amountCents
    ): ?WalletTransaction {

        if ($amountCents < 0) {
            throw new \InvalidArgumentException(
                'El importe no puede ser negativo.'
            );
        }

        if ($amountCents === 0) {
            return null;
        }

        if (DB::transactionLevel() === 0) {
            throw new \LogicException(
                'La reserva debe ejecutarse dentro de una transacción.'
            );
        }

        if (!$order->user_id) {
            throw ValidationException::withMessages([
                'wallet' => 'Debes iniciar sesión para usar tu saldo.',
            ]);
        }

        if (
            $order->payment_status !== Order::PAYMENT_PENDING ||
            $order->status !== Order::STATUS_PENDING
        ) {
            throw new \LogicException(
                'El pedido no está pendiente.'
            );
        }

        $reference = 'wallet_reserve:' . $order->id;

        // La creación del pedido debe preceder a esta reserva.
        // Bloqueamos el monedero para evitar gastos simultáneos.
        $wallet = Wallet::query()
            ->where('user_id', $order->user_id)
            ->lockForUpdate()
            ->first();

        if (!$wallet) {
            throw ValidationException::withMessages([
                'wallet' => 'No tienes saldo disponible.',
            ]);
        }

        $existing = WalletTransaction::query()
            ->where('reference', $reference)
            ->first();

        if ($existing) {
            throw new \LogicException(
                'Este pedido ya tiene una reserva de saldo.'
            );
        }

        if ($this->toCents($order->wallet_discount ?? '0.00') > 0) {
            throw new \LogicException(
                'El pedido ya tiene saldo aplicado.'
            );
        }

        $beforeCents = $this->toCents($wallet->balance);

        if ($amountCents > $beforeCents) {
            throw ValidationException::withMessages([
                'wallet' => 'Tu saldo disponible es insuficiente.',
            ]);
        }

        $orderTotalCents = $this->toCents($order->total);

        // En esta etapa, total todavía representa
        // el importe antes de aplicar el monedero.
        if ($amountCents > $orderTotalCents) {
            throw ValidationException::withMessages([
                'wallet' => 'El saldo supera el importe del pedido.',
            ]);
        }

        $afterCents = $beforeCents - $amountCents;

        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'order_id' => $order->id,
            'type' => WalletTransaction::TYPE_DEBIT,
            'amount' => $this->toMoney($amountCents),
            'balance_before' => $this->toMoney($beforeCents),
            'balance_after' => $this->toMoney($afterCents),
            'reference' => $reference,
            'description' => 'Saldo reservado para pedido '
                . $order->order_number,
        ]);

        $wallet->update([
            'balance' => $this->toMoney($afterCents),
        ]);

        $order->update([
            'wallet_discount' => $this->toMoney($amountCents),
            'total' => $this->toMoney(
                $orderTotalCents - $amountCents
            ),
        ]);

        return $transaction;
    }



    /**
     * Devuelve el saldo utilizado en un pedido cancelado.
     *
     * Debe ejecutarse dentro de la misma transacción
     * que cancela el pedido y restaura el inventario.
     */
    public function refundBalance(Order $order): ?WalletTransaction
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException(
                'La devolución debe ejecutarse dentro de una transacción.'
            );
        }

        $reference = 'wallet_refund:' . $order->id;

        $existing = WalletTransaction::query()
            ->where('reference', $reference)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Verificamos que realmente exista una reserva.
        $reservation = WalletTransaction::query()
            ->where('order_id', $order->id)
            ->where('reference', 'wallet_reserve:' . $order->id)
            ->where('type', WalletTransaction::TYPE_DEBIT)
            ->first();

        if (!$reservation) {
            return null;
        }

        $wallet = Wallet::query()
            ->whereKey($reservation->wallet_id)
            ->lockForUpdate()
            ->firstOrFail();

        // Comprobamos nuevamente bajo el bloqueo.
        $existing = WalletTransaction::query()
            ->where('reference', $reference)
            ->first();

        if ($existing) {
            return $existing;
        }

        $amountCents = $this->toCents($reservation->amount);
        $beforeCents = $this->toCents($wallet->balance);
        $afterCents = $beforeCents + $amountCents;

        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'order_id' => $order->id,
            'type' => WalletTransaction::TYPE_REFUND,
            'amount' => $this->toMoney($amountCents),
            'balance_before' => $this->toMoney($beforeCents),
            'balance_after' => $this->toMoney($afterCents),
            'reference' => $reference,
            'description' => 'Devolución de saldo por cancelación '
                . $order->order_number,
        ]);

        $wallet->update([
            'balance' => $this->toMoney($afterCents),
        ]);

        return $transaction;
    }

}
