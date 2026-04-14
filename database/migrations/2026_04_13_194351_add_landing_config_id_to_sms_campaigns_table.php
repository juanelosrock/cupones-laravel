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
        Schema::table('sms_campaigns', function (Blueprint $table) {
            $table->foreignId('landing_config_id')
                  ->nullable()
                  ->after('campaign_id')
                  ->constrained('landing_page_configs')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_campaigns', function (Blueprint $table) {
            $table->dropForeign(['landing_config_id']);
            $table->dropColumn('landing_config_id');
        });
    }
};
