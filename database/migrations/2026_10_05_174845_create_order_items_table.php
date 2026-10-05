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
        Schema::create('order_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Nullable intencionalmente.
             *
             * Si algún día el producto desaparece,
             * el detalle histórico del pedido sigue
             * existiendo gracias al snapshot.
             */
            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             * Snapshot del producto.
             */
            $table->string('product_name');
            $table->string('product_sku');

            $table->decimal('unit_price', 10, 2);

            $table->unsignedInteger('quantity');

            $table->decimal('subtotal', 10, 2);

            $table->timestamps();

            $table->index('product_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
