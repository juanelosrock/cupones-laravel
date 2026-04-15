<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliar ENUM channel: añade 'pos' y 'app' (alinea con validación del controller)
        DB::statement("ALTER TABLE coupon_redemptions MODIFY COLUMN channel ENUM('api','web','manual','sms','pos','app') NOT NULL DEFAULT 'api'");
    }

    public function down(): void
    {
        DB::statement("UPDATE coupon_redemptions SET channel = 'api' WHERE channel IN ('pos','app')");
        DB::statement("ALTER TABLE coupon_redemptions MODIFY COLUMN channel ENUM('api','web','manual','sms') NOT NULL DEFAULT 'api'");
    }
};
