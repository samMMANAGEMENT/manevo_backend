<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    // Log de eventos disparados por un addon (envíos de n8n, etc.) — también sirve de idempotencia.
    public function up(): void
    {
        Schema::create('addon_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained('addons')->cascadeOnDelete();
            $table->string('event_type'); // reminder_sent, payment_notified, appointment_booked...
            $table->string('reference_type')->nullable(); // App\Http\Modules\Appointment\Model\Appointment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('status')->default('sent'); // sent, failed
            $table->string('external_id')->nullable(); // id del mensaje en YCloud/WhatsApp
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['addon_id', 'entity_id', 'reference_type', 'reference_id', 'event_type'], 'addon_events_idempotency_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_events');
    }
};
