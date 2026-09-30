<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_school_year_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discount_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['student_school_year_id', 'discount_type_id'], 'ssy_discount_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_school_year_discounts');
    }
};
