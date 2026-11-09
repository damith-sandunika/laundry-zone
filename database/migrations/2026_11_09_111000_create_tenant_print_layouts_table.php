<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $conn = Schema::connection('landlord');

        if (!$conn->hasTable('tenant_print_layouts')) {
            $conn->create('tenant_print_layouts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('type', 50); // 'bill', 'receipt', 'invoice'
                $table->string('name')->default('Default Layout');
                $table->string('font_family')->default('Inter');
                $table->string('font_size')->default('12px');
                $table->string('primary_color', 30)->default('#000000');
                $table->string('accent_color', 30)->default('#2563eb');
                $table->boolean('show_logo')->default(true);
                $table->boolean('show_header')->default(true);
                $table->boolean('show_branch_info')->default(true);
                $table->boolean('show_customer_info')->default(true);
                $table->boolean('show_qr_code')->default(true);
                $table->boolean('show_barcode')->default(true);
                $table->boolean('show_notes')->default(true);
                $table->boolean('show_footer')->default(true);
                $table->string('header_title')->nullable();
                $table->string('header_subtitle')->nullable();
                $table->text('terms_and_conditions')->nullable();
                $table->text('footer_text')->nullable();
                $table->json('custom_fields')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'type']);
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('tenant_print_layouts');
    }
};

