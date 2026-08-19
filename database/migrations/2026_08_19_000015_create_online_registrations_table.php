<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('patient_name');
            $table->string('nik')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->default('L');
            $table->string('poli');
            $table->string('complaint')->nullable();
            $table->date('registration_date');
            $table->string('queue_number');
            $table->string('status')->default('registered');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_registrations');
    }
};