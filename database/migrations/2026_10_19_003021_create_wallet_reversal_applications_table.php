<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_reversal_applications', function (Blueprint $table) {
            $table->id();

            // Recompensa utilizada para compensar la deuda.
            $table->foreignId('wallet_transaction_id')
                ->constrained('wallet_transactions')
                ->restrictOnDelete();

            // Deuda que recibió la compensación.
            $table->foreignId('wallet_reversal_id')
                ->constrained('wallet_reversals')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);

            // Se llena si la recompensa se cancela
            // y debemos deshacer su compensación.
            $table->timestamp('reversed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['wallet_transaction_id', 'wallet_reversal_id'],
                'wallet_reversal_application_unique'
            );

            $table->index([
                'wallet_reversal_id',
                'reversed_at',
            ], 'wallet_reversal_application_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_reversal_applications');
    }
};
