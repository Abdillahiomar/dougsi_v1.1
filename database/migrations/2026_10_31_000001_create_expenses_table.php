<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accounting_account_id')->constrained()->restrictOnDelete(); // compte de charge (classe 6)
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->unsignedInteger('amount');
            $table->date('date');
            $table->string('payment_method'); // cash, bank
            $table->string('receipt_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
