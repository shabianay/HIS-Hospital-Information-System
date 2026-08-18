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
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->float('temperature')->nullable(); // Celsius
            $table->integer('blood_pressure_systolic')->nullable(); // mmHg
            $table->integer('blood_pressure_diastolic')->nullable(); // mmHg
            $table->integer('heart_rate')->nullable(); // bpm
            $table->integer('respiratory_rate')->nullable(); // breaths/min
            $table->float('weight')->nullable(); // kg
            $table->float('height')->nullable(); // cm
            $table->integer('oxygen_saturation')->nullable(); // % SpO2
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
