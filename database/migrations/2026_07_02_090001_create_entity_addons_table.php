<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    // Qué addons tiene activos cada entidad.
    public function up(): void
    {
        Schema::create('entity_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained('addons')->cascadeOnDelete();
            $table->string('status')->default('active')->index(); // active, cancelled
            $table->timestamp('activated_at')->nullable();
            $table->date('current_period_end')->nullable(); // null = sin vencimiento fijo (renovación manual)
            $table->timestamps();

            $table->unique(['entity_id', 'addon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_addons');
    }
};
