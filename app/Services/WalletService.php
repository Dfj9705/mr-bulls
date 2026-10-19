<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\WalletReversal;
use App\Models\WalletReversalApplication;
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

            if (
                $order->payment_status !== Order::PAYMENT_PAID ||
                $order->status === Order::STATUS_CANCELLED ||
                !$order->user_id
            ) {
                return null;
            }

            $reference = 'purchase_reward:' . $order->id;

            // Evitar acreditar dos veces la misma compra.
            $existing = WalletTransaction::query()
                ->where('reference', $reference)
                ->first();

            if ($existing) {
                return $existing;
            }


            $subtotalCents = $this->toCents($order->subtotal);

            $walletDiscountCents = $this->toCents(
                $order->wallet_discount ?? '0.00'
            );

            // Solo los productos, después de aplicar el monedero.
// No se incluyen gastos de envío.
            $rewardBaseCents = max(
                0,
                $subtotalCents - $walletDiscountCents
            );

            // Recompensa del 2%, redondeada a centavos.
            $rewardCents = (int) round(
                $rewardBaseCents * 0.02,
                0,
                PHP_ROUND_HALF_UP
            );

            if ($rewardCents <= 0) {
                return null;
            }


            if ($rewardCents <= 0) {
                return null;
            }

            Wallet::firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => '0.00', 'pending_reversal' => '0.00']
            );

            $wallet = Wallet::query()
                ->where('user_id', $order->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Segunda comprobación dentro del bloqueo.
            $existing = WalletTransaction::query()
                ->where('reference', $reference)
                ->first();

            if ($existing) {
                return $existing;
            }

            $beforeCents = $this->toCents($wallet->balance);

            $pendingBeforeCents = $this->toCents(
                $wallet->pending_reversal ?? '0.00'
            );

            $remainingRewardCents = $rewardCents;
            $compensatedCents = 0;

            // Deudas pendientes, de la más antigua a la más reciente.
            $reversals = WalletReversal::query()
                ->where('wallet_id', $wallet->id)
                ->where('pending_amount', '>', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Crear el movimiento de recompensa.
            // Las aplicaciones se registrarán a continuación.
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'order_id' => $order->id,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $this->toMoney($rewardCents),
                'credited_amount' => '0.00',
                'compensated_amount' => '0.00',
                'balance_before' => $this->toMoney($beforeCents),
                'balance_after' => $this->toMoney($beforeCents),
                'reference' => $reference,
                'description' => 'Recompensa del 2% por compra '
                    . $order->order_number,
            ]);

            foreach ($reversals as $reversal) {

                if ($remainingRewardCents <= 0) {
                    break;
                }

                $pendingCents = $this->toCents(
                    $reversal->pending_amount
                );

                $appliedCents = min(
                    $remainingRewardCents,
                    $pendingCents
                );

                if ($appliedCents <= 0) {
                    continue;
                }

                WalletReversalApplication::create([
                    'wallet_transaction_id' => $transaction->id,
                    'wallet_reversal_id' => $reversal->id,
                    'amount' => $this->toMoney($appliedCents),
                    'reversed_at' => null,
                ]);

                $reversal->update([
                    'pending_amount' => $this->toMoney(
                        $pendingCents - $appliedCents
                    ),
                ]);

                $remainingRewardCents -= $appliedCents;
                $compensatedCents += $appliedCents;
            }

            $creditedCents = $remainingRewardCents;

            if ($compensatedCents > $pendingBeforeCents) {
                throw new \LogicException(
                    'Las compensaciones superan la reversión pendiente.'
                );
            }

            $afterCents = $beforeCents + $creditedCents;

            $pendingAfterCents =
                $pendingBeforeCents - $compensatedCents;

            // Actualizar el monedero.
            $wallet->update([
                'balance' => $this->toMoney($afterCents),
                'pending_reversal' => $this->toMoney(
                    $pendingAfterCents
                ),
            ]);

            // Actualizar el desglose definitivo de la recompensa.
            $transaction->update([
                'credited_amount' => $this->toMoney(
                    $creditedCents
                ),
                'compensated_amount' => $this->toMoney(
                    $compensatedCents
                ),
                'balance_after' => $this->toMoney($afterCents),
                'description' => sprintf(
                    'Recompensa del 2%% por compra %s. '
                    . 'Acreditado: Q%s. Compensado: Q%s.',
                    $order->order_number,
                    $this->toMoney($creditedCents),
                    $this->toMoney($compensatedCents)
                ),
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

    /**
     * Revierte la recompensa de una compra cancelada.
     *
     * Si el saldo es insuficiente, registra la diferencia
     * como reversión pendiente.
     *
     * Debe ejecutarse dentro de una transacción de BD,
     * con el pedido bloqueado previamente.
     */

    public function reversePurchaseReward(Order $order): ?WalletTransaction
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException(
                'La reversión debe ejecutarse dentro de una transacción.'
            );
        }

        if ($order->status !== Order::STATUS_CANCELLED) {
            throw new \LogicException(
                'El pedido debe estar cancelado.'
            );
        }

        $reference = 'purchase_reversal:' . $order->id;

        // Localizar la recompensa original.
        $reward = WalletTransaction::query()
            ->where('reference', 'purchase_reward:' . $order->id)
            ->where('type', WalletTransaction::TYPE_CREDIT)
            ->first();

        if (!$reward) {
            return null;
        }

        // Serializar las operaciones del monedero.
        $wallet = Wallet::query()
            ->whereKey($reward->wallet_id)
            ->lockForUpdate()
            ->firstOrFail();

        // Evitar reversiones duplicadas.
        $existing = WalletTransaction::query()
            ->where('reference', $reference)
            ->first();

        if ($existing) {
            return $existing;
        }

        $rewardCents = $this->toCents($reward->amount);

        // Compatibilidad con recompensas antiguas
        // que se acreditaron íntegramente.
        $compensatedCents = $this->toCents(
            $reward->compensated_amount ?? '0.00'
        );

        $creditedCents = $this->toCents(
            $reward->credited_amount
            ?? $this->toMoney($rewardCents - $compensatedCents)
        );

        if (
            $creditedCents < 0 ||
            $compensatedCents < 0 ||
            $creditedCents + $compensatedCents !== $rewardCents
        ) {
            throw new \LogicException(
                'El desglose de la recompensa es inconsistente.'
            );
        }

        $applications = WalletReversalApplication::query()
            ->where('wallet_transaction_id', $reward->id)
            ->whereNull('reversed_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $applicationsCents = 0;

        foreach ($applications as $application) {
            $applicationsCents += $this->toCents(
                $application->amount
            );
        }

        // No revertir una recompensa compensada si no
        // existe la trazabilidad de sus aplicaciones.
        if ($applicationsCents !== $compensatedCents) {
            throw new \LogicException(
                'Las aplicaciones no coinciden con '
                . 'el importe compensado.'
            );
        }

        $beforeCents = $this->toCents($wallet->balance);

        $pendingBeforeCents = $this->toCents(
            $wallet->pending_reversal ?? '0.00'
        );

        // Revertir únicamente lo acreditado al saldo.
        $deductedCents = min($beforeCents, $creditedCents);

        $newPendingCents = $creditedCents - $deductedCents;

        $afterCents = $beforeCents - $deductedCents;

        // Reabrir las deudas compensadas por esta recompensa.
        $reopenedCents = 0;

        foreach ($applications as $application) {
            $reversal = WalletReversal::query()
                ->whereKey($application->wallet_reversal_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($reversal->wallet_id !== $wallet->id) {
                throw new \LogicException(
                    'La aplicación pertenece a otro monedero.'
                );
            }

            $appliedCents = $this->toCents(
                $application->amount
            );

            $currentPendingCents = $this->toCents(
                $reversal->pending_amount
            );

            $reversal->update([
                'pending_amount' => $this->toMoney(
                    $currentPendingCents + $appliedCents
                ),
            ]);

            $application->update([
                'reversed_at' => now(),
            ]);

            $reopenedCents += $appliedCents;
        }

        // Crear la obligación correspondiente al pedido
        // cuya recompensa estamos cancelando.
        WalletReversal::create([
            'wallet_id' => $wallet->id,
            'order_id' => $order->id,
            'original_amount' => $this->toMoney($rewardCents),
            'pending_amount' => $this->toMoney($newPendingCents),
        ]);

        $pendingAfterCents =
            $pendingBeforeCents
            + $reopenedCents
            + $newPendingCents;

        $wallet->update([
            'balance' => $this->toMoney($afterCents),
            'pending_reversal' => $this->toMoney(
                $pendingAfterCents
            ),
        ]);

        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'order_id' => $order->id,
            'type' => WalletTransaction::TYPE_REVERSAL,
            'amount' => $this->toMoney($rewardCents),
            'balance_before' => $this->toMoney($beforeCents),
            'balance_after' => $this->toMoney($afterCents),
            'reference' => $reference,
            'description' => sprintf(
                'Reversión de recompensa del pedido %s. '
                . 'Descontado: Q%s. '
                . 'Deuda reabierta: Q%s. '
                . 'Nueva deuda: Q%s.',
                $order->order_number,
                $this->toMoney($deductedCents),
                $this->toMoney($reopenedCents),
                $this->toMoney($newPendingCents)
            ),
        ]);
    }


}
