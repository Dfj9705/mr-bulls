<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
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
}
