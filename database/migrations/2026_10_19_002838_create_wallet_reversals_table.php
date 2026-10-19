<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('wallet_reversals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('order_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('original_amount', 12, 2);
            $table->decimal('pending_amount', 12, 2)
                ->default(0);

            $table->timestamps();

            $table->unique('order_id');
            $table->index(['wallet_id', 'pending_amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_reversals');
    }

};
