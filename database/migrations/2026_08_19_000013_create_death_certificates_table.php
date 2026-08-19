<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('death_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->timestamp('date_of_death');
            $table->string('place_of_death')->default('Rumah Sakit');
            $table->string('cause_of_death');
            $table->string('diagnosis')->nullable();
            $table->string('deceased_relation')->nullable();
            $table->string('reporter_name')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('doctor_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('death_certificates');
    }
};