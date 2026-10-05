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
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            /*
             * Cliente registrado.
             * Null = compra como invitado.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             * Número público del pedido.
             * Ejemplo: MB-20261005-000001
             */
            $table->string('order_number')->unique();

            /*
             * Datos del comprador.
             * Se guardan aunque exista user_id para
             * conservar el snapshot histórico.
             */
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30);

            /*
             * Snapshot de dirección de entrega.
             */
            $table->string('shipping_department', 100);
            $table->string('shipping_municipality', 100);
            $table->string('shipping_address');
            $table->text('shipping_references')->nullable();

            /*
             * Importes.
             */
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            /*
             * Estado general del pedido.
             */
            $table->string('status', 30)->default('pending');

            /*
             * Estado del pago.
             */
            $table->string('payment_status', 30)->default('pending');

            /*
             * Método de pago.
             *
             * Inicialmente trabajaremos manualmente,
             * pero dejamos preparado el campo para
             * una futura pasarela.
             */
            $table->string('payment_method', 50)->nullable();

            /*
             * Link de pago generado/colocado
             * manualmente por administración.
             */
            $table->text('payment_url')->nullable();

            /*
             * Notas del cliente.
             */
            $table->text('customer_notes')->nullable();

            /*
             * Notas internas del administrador.
             */
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('payment_status');
            $table->index('customer_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
