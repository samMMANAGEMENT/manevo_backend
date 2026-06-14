<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();

            // Electronic Billing Info
            $table->string('number')->nullable();
            $table->string('prefix')->nullable();
            $table->string('cufe')->nullable();
            $table->string('status')->default('draft'); // draft, sent, error, canceled

            // Customer Info (Stored at time of billing)
            $table->string('customer_name')->nullable();
            $table->string('customer_identification')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();

            // Totals
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);

            // External provider references
            $table->string('external_id')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('xml_url')->nullable();
            $table->text('provider_response')->nullable();

            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            // Polymorphic link to Sales or ServiceOrders
            $table->nullableMorphs('invoicable');

            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 16, 2);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2);

            $table->timestamps();
        });

        // Add index to sales and service_orders to track if they are invoiced (optional but recommended)
        /* 
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('billing_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
        });
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignId('billing_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
        });
        */
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
