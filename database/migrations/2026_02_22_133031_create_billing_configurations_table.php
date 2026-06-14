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
        Schema::create('billing_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();

            // Basic Business Info (for DIAN)
            $table->string('razon_social');
            $table->string('document_type')->default('NIT'); // NIT, CC, CE
            $table->string('nit'); // This field will store the ID number regardless of type
            $table->string('dv', 1)->nullable(); // Verification digit
            $table->string('email_billing')->nullable();
            $table->string('phone_billing')->nullable();
            $table->string('address_billing')->nullable();
            $table->string('city_billing')->nullable();

            // Fiscal Info
            $table->string('tax_regime')->nullable(); // e.g., Responsable de IVA, No responsable

            // DIAN Resolution
            $table->string('resolution_number')->nullable();
            $table->date('resolution_date')->nullable();
            $table->string('prefix')->nullable();
            $table->integer('start_range')->nullable();
            $table->integer('end_range')->nullable();

            // API Keys / Software Info (DataInvoice specific or general)
            $table->string('software_id')->nullable();
            $table->string('software_pin')->nullable();
            $table->text('api_token')->nullable(); // Token for external API
            $table->string('api_base_url')->nullable(); // External API Base URL
            $table->string('test_set_id')->nullable();
            $table->boolean('is_test')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_configurations');
    }
};
