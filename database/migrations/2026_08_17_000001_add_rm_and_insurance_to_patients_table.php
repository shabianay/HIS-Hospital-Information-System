<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('rm_number')->nullable()->unique()->after('nik');
            $table->string('insurance_provider')->nullable()->after('phone_number');
            $table->string('insurance_number')->nullable()->after('insurance_provider');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['rm_number', 'insurance_provider', 'insurance_number']);
        });
    }
};
