<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // "Exercice 2026"
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('status')->default('open'); // open, closed
            $table->boolean('is_active')->default(false); // exercice courant proposé par défaut
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_years');
    }
};
