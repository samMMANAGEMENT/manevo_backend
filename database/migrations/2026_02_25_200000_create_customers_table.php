<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();

            $table->string('name');
            $table->string('identification_type')->default('CC'); // CC, NIT, etc.
            $table->string('identification_number');
            $table->string('dv', 1)->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();

            // Electronic Billing Specifics
            $table->integer('municipality_id')->default(822); // Default Bogotá
            $table->integer('type_regime_id')->default(2);    // No responsable IVA
            $table->integer('type_organization_id')->default(2); // Persona Natural

            $table->boolean('status')->default(true);
            $table->timestamps();

            // Unique identification per entity
            $table->unique(['entity_id', 'identification_number'], 'entity_customer_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
