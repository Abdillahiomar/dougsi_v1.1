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
        Schema::create('receipt_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_receipt_id')->constrained()->cascadeOnDelete();
            $table->string('method');
            $table->unsignedBigInteger('amount');
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index('payment_receipt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_methods');
    }
};
