<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('Rontgen');
            $table->string('unit')->nullable();
            $table->text('reference_range')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('radiology_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_urgent')->default(false);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('clinical_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('radiology_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radiology_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('radiology_test_id')->constrained()->cascadeOnDelete();
            $table->string('test_name');
            $table->text('reference_range')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->text('result_findings')->nullable();
            $table->text('result_impression')->nullable();
            $table->enum('result_status', ['pending', 'normal', 'abnormal'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_request_items');
        Schema::dropIfExists('radiology_requests');
        Schema::dropIfExists('radiology_tests');
    }
};