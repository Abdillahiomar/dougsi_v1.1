<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "recorded_by" représente le dernier membre du staff à avoir modifié
     * la ligne (il est écrasé à chaque mise à jour). "created_by" fixe
     * l'auteur original, jamais réécrit — a minima une piste d'audit.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('recorded_by')
                ->constrained('staff')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
