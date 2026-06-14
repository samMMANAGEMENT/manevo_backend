<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    // Qué plan tiene cada entidad (y cuándo vence).
    public function up(): void
    {
        Schema::create('entity_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities');
            $table->foreignId('plan_id')->constrained('plans');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = ilimitado
            $table->string('status')->default('active')->index(); // active, past_due, cancelled, trial
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_plan');
    }
};
