<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Delivery Appointments - Customer requests a pickup
        Schema::create('delivery_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('pickup_address');
            $table->string('delivery_address')->nullable();
            $table->dateTime('pickup_scheduled_at');
            $table->dateTime('delivery_scheduled_at')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'rider_assigned', 'picked_up', 'at_branch', 'processing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();
        });

        // Delivery Tasks - Tasks assigned to riders
        Schema::create('delivery_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('rider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->enum('task_type', ['pickup', 'delivery'])->default('pickup');
            $table->enum('status', ['assigned', 'accepted', 'on_way', 'arrived', 'completed', 'cancelled'])->default('assigned');
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->decimal('rider_lat', 10, 7)->nullable();
            $table->decimal('rider_lng', 10, 7)->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Workshop Dispatches - Branch -> Workshop -> Branch flow
        Schema::create('workshop_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade'); // originating branch
            $table->unsignedBigInteger('workshop_branch_id')->nullable(); // workshop branch (if applicable)
            $table->foreignId('dispatched_by')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('received_by')->nullable(); // workshop staff who received
            $table->unsignedBigInteger('returned_by')->nullable(); // who sent it back
            $table->enum('status', [
                'dispatched_to_workshop',
                'received_at_workshop',
                'processing',
                'completed_at_workshop',
                'returned_to_branch',
                'received_at_branch'
            ])->default('dispatched_to_workshop');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at_workshop_at')->nullable();
            $table->timestamp('completed_at_workshop_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('received_at_branch_at')->nullable();
            $table->text('dispatch_notes')->nullable();
            $table->text('workshop_notes')->nullable();
            $table->timestamps();

            $table->foreign('workshop_branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('returned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_dispatches');
        Schema::dropIfExists('delivery_tasks');
        Schema::dropIfExists('delivery_appointments');
    }
};
