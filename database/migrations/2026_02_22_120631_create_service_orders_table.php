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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->decimal('total_net', 12, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'mixed']);
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->decimal('transfer_amount', 12, 2)->default(0);
            $table->string('transaction_token')->unique()->nullable(); // Para evitar duplicados
            $table->timestamp('date');
            $table->timestamps();

            // Índices para reportes rápidos
            $table->index(['entity_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
