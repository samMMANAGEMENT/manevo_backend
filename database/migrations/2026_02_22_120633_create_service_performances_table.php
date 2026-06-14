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
        Schema::create('service_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('service_orders')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade'); // El empleado

            $table->integer('quantity')->default(1);

            // Snapshots financieros
            $table->decimal('price_snapshot', 12, 2);
            $table->decimal('commission_percentage_snapshot', 5, 2);

            // Contabilidad de la línea
            $table->decimal('total_gross', 12, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2);

            // Pago proporcional (para auditoría de caja)
            $table->decimal('proportional_cash', 12, 2)->default(0);
            $table->decimal('proportional_transfer', 12, 2)->default(0);

            // Estado de pago a empleado
            $table->boolean('is_paid_to_employee')->default(false);

            $table->timestamps();

            // Índices sugeridos por el usuario
            $table->index(['operator_id', 'is_paid_to_employee']);
            $table->index(['service_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_performances');
    }
};
