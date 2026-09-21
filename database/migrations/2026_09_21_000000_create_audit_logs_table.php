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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('guard', 20);   // 'web' ou 'superadmin'
            $table->string('event', 20);   // 'login' ou 'logout'
            $table->nullableMorphs('user'); // user_id + user_type (User ou SuperAdmin)

            // Copie figée au moment de l'évènement (reste lisible même si le compte change/est supprimé)
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
