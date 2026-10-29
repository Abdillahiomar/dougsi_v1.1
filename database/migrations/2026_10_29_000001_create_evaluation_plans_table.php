<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // "Trimestre 1", "Trimestre 2"...
            $table->string('label'); // "Devoir 1", "Contrôle", "Examen"...
            $table->unsignedTinyInteger('max_score')->default(20);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['level_id', 'subject_id', 'period', 'label'], 'evaluation_plans_unique_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_plans');
    }
};
