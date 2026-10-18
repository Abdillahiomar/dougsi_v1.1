<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Un abonnement peut être 100% négocié, sans plan de référence
     * (voir subscriptions/edit.blade.php et schools/show.blade.php).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE subscriptions ALTER COLUMN plan_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE subscriptions ALTER COLUMN plan_id SET NOT NULL');
    }
};
