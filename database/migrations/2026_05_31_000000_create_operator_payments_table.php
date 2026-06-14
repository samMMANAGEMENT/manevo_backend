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
        Schema::create('operator_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('status')->default('Paid'); // Paid, Pending
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // The user who recorded the payment
            $table->timestamps();
        });

        Schema::create('operator_payment_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_payment_id')->constrained('operator_payments')->cascadeOnDelete();
            $table->foreignId('service_performance_id')->constrained('service_performances')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_payment_performances');
        Schema::dropIfExists('operator_payments');
    }
};
