<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('triage_level')->default('green');
            $table->string('chief_complaint');
            $table->text('triage_notes')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->integer('blood_pressure_systolic')->nullable();
            $table->integer('blood_pressure_diastolic')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->integer('oxygen_saturation')->nullable();
            $table->integer('gcs')->nullable();
            $table->enum('status', ['waiting', 'in_triage', 'treatment', 'observation', 'admitted', 'discharged', 'referred', 'deceased'])->default('waiting');
            $table->string('referred_to')->nullable();
            $table->string('discharge_notes')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('discharged_at')->nullable();
            $table->foreignId('discharged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_visits');
    }
};