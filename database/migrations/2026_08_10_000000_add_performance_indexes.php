<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan index untuk kolom yang sering di-filter / di-sort,
     * agar query tetap cepat saat data bertambah banyak.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->index('name');
            $table->index('created_at');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index('appointment_date');
            $table->index('status');
            $table->index('created_at');
            $table->index(['poli_id', 'appointment_date']);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('status');
            $table->index(['status', 'created_at']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('diagnoses', function (Blueprint $table) {
            $table->index('icd_code');
            $table->index(['medical_record_id', 'icd_code']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->index('name');
            $table->index(['is_active', 'name']);
        });

        Schema::table('medicine_stocks', function (Blueprint $table) {
            $table->index(['medicine_id', 'batch_number']);
            $table->index(['expiry_date']);
        });

        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['doctor_id', 'poli_id', 'day_of_week']);
            $table->index(['is_active']);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index(['medicine_id', 'is_dispensed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['appointment_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['poli_id', 'appointment_date']);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropIndex(['icd_code']);
            $table->dropIndex(['medical_record_id', 'icd_code']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['is_active', 'name']);
        });

        Schema::table('medicine_stocks', function (Blueprint $table) {
            $table->dropIndex(['medicine_id', 'batch_number']);
            $table->dropIndex(['expiry_date']);
        });

        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'poli_id', 'day_of_week']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex(['medicine_id', 'is_dispensed']);
        });
    }
};