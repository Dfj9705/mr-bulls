<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('category_bundle');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->decimal('eligible_price', 10, 2);
            $table->unsignedInteger('bundle_quantity');
            $table->decimal('bundle_price', 10, 2);
            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'category_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('promotion_discount', 12, 2)->default(0);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('base_unit_price', 10, 2)->nullable();
            $table->decimal('promotion_discount', 12, 2)->default(0);
            $table->json('promotion_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn(['base_unit_price', 'promotion_discount', 'promotion_snapshot']));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('promotion_discount'));
        Schema::dropIfExists('promotions');
    }
};
