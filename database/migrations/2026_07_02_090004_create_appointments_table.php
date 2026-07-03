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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();

            $table->timestamp('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(60);

            $table->string('status')->default('pending')->index(); // pending, confirmed, completed, cancelled, no_show
            $table->string('payment_status')->default('unpaid')->index(); // unpaid, paid

            $table->decimal('price', 12, 2)->nullable();
            $table->string('source')->default('manual'); // manual, whatsapp_bot
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['entity_id', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
