<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('manual_discount', 12, 2)
                ->default(0);

            $table->string('discount_type', 20)
                ->nullable();

            $table->decimal('discount_value', 12, 2)
                ->nullable();

            $table->text('discount_reason')
                ->nullable();

            $table->foreignId('discount_applied_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'discount_applied_by'
            );

            $table->dropColumn([
                'manual_discount',
                'discount_type',
                'discount_value',
                'discount_reason',
            ]);
        });
    }
};
