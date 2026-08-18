<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['cash', 'card', 'qris', 'bpjs', 'insurance'])->default('cash');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reference')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_payments');
    }
};