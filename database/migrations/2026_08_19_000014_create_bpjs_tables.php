<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sep_records', function (Blueprint $table) {
            $table->id();
            $table->string('sep_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bpjs_number');
            $table->string('jenis_pelayanan')->default('rawat_jalan');
            $table->date('sep_date');
            $table->string('diagnosis')->nullable();
            $table->string('poli')->nullable();
            $table->string('faskes_perujuk')->nullable();
            $table->string('status')->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bpjs_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();
            $table->foreignId('sep_record_id')->nullable()->constrained('sep_records')->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->date('claim_date');
            $table->decimal('total_claim', 12, 2)->default(0);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->string('status')->default('diajukan');
            $table->string('jenis_klaim')->default('rawat_jalan');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_claims');
        Schema::dropIfExists('sep_records');
    }
};