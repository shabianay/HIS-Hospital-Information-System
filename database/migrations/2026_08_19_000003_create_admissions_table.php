<?php

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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->constrained()->onDelete('cascade');
            $table->enum('admission_type', ['elective', 'emergency'])->default('elective');
            $table->enum('status', ['admitted', 'discharged', 'cancelled'])->default('admitted');
            $table->dateTime('admitted_at');
            $table->dateTime('discharged_at')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('discharge_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('admitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('discharged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};