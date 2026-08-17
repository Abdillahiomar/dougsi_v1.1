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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('custom_monthly_amount', 12, 2)->nullable()->after('plan_id');
        $table->decimal('discount_percent', 5, 2)->default(0)->after('custom_monthly_amount');
        $table->string('billing_cycle')->default('monthly')->after('discount_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
             $table->dropColumn(['custom_monthly_amount', 'discount_percent', 'billing_cycle']);
        });
    }
};
