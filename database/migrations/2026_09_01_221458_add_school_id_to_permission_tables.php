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
        // Rôles : ajouter school_id (nullable pour les rôles globaux éventuels)
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->after('id');
            // L'unicité du nom de rôle devient par école
            $table->dropUnique(['name', 'guard_name']);
            $table->unique(['name', 'guard_name', 'school_id']);
        });

        // Table pivot model_has_roles : ajouter school_id
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
        });

        // Table pivot model_has_permissions : ajouter school_id
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_tables', function (Blueprint $table) {
            //
        });
    }
};
