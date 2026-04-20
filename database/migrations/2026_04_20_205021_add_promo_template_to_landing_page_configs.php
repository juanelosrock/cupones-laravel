<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE landing_page_configs MODIFY COLUMN template ENUM('minimal','branded','hero','promo') NOT NULL DEFAULT 'minimal'");
    }

    public function down(): void
    {
        DB::statement("UPDATE landing_page_configs SET template = 'minimal' WHERE template = 'promo'");
        DB::statement("ALTER TABLE landing_page_configs MODIFY COLUMN template ENUM('minimal','branded','hero') NOT NULL DEFAULT 'minimal'");
    }
};
