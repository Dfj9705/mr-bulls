<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReconcileWalletRewards extends Command
{
    protected $signature = 'wallet:reconcile-rewards';

    protected $description =
        'Acredita recompensas pendientes de pedidos pagados.';

    public function handle(WalletService $walletService): int
    {


        if (!config('loyalty.enabled', false)) {
            $this->warn(
                'El programa de lealtad está desactivado. '
                . 'No se procesaron recompensas.'
            );

            return self::SUCCESS;
        }

        $startDate = config('loyalty.start_date');

        if (app()->environment('production') && !$startDate) {
            $this->warn(
                'No se ha configurado la fecha oficial de lanzamiento. '
                . 'No se procesaron recompensas.'
            );

            return self::SUCCESS;
        }

        $processed = 0;
        $credited = 0;
        $failed = 0;

        Order::query()

            ->when($startDate, function ($query) use ($startDate) {
                $query->where(
                    'created_at',
                    '>=',
                    \Carbon\Carbon::parse(
                        $startDate,
                        config('app.timezone')
                    )->startOfDay()
                );
            })

            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('wallet_transactions')
                    ->whereColumn(
                        'wallet_transactions.order_id',
                        'orders.id'
                    )
                    ->where(
                        'wallet_transactions.type',
                        WalletTransaction::TYPE_CREDIT
                    )
                    ->whereRaw(
                        "wallet_transactions.reference = "
                        . "CONCAT('purchase_reward:', orders.id)"
                    );
            })
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($walletService, &$processed, &$credited, &$failed) {
                foreach ($orders as $order) {
                    $processed++;

                    try {
                        $transaction = $walletService
                            ->creditPurchase($order);

                        if ($transaction) {
                            $credited++;
                        }
                    } catch (Throwable $exception) {
                        $failed++;

                        Log::error(
                            'Error al reconciliar recompensa.',
                            [
                                'order_id' => $order->id,
                                'error' => $exception->getMessage(),
                            ]
                        );

                        $this->error(
                            "Error en pedido #{$order->id}: "
                            . $exception->getMessage()
                        );
                    }
                }
            });

        $this->info(
            "Revisados: {$processed}. "
            . "Acreditados: {$credited}. "
            . "Errores: {$failed}."
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
