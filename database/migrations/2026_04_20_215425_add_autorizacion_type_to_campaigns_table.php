<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE campaigns MODIFY COLUMN type ENUM('general','sms','product','activation','autorizacion') NOT NULL DEFAULT 'general'");
    }

    public function down(): void
    {
        DB::statement("UPDATE campaigns SET type = 'general' WHERE type = 'autorizacion'");
        DB::statement("ALTER TABLE campaigns MODIFY COLUMN type ENUM('general','sms','product','activation') NOT NULL DEFAULT 'general'");
    }
};
