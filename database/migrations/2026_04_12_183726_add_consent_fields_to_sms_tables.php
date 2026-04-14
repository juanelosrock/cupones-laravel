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
            $table->boolean('send_consent_link')->default(false)->after('coupon_batch_id');
        });

        Schema::table('sms_recipients', function (Blueprint $table) {
            $table->string('consent_token', 64)->nullable()->unique()->after('phone');
            $table->timestamp('consent_accepted_at')->nullable()->after('consent_token');
            $table->string('assigned_coupon_code', 50)->nullable()->after('consent_accepted_at');
            $table->string('acceptance_ip', 45)->nullable()->after('assigned_coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('sms_campaigns', function (Blueprint $table) {
            $table->dropColumn('send_consent_link');
        });

        Schema::table('sms_recipients', function (Blueprint $table) {
            $table->dropColumn(['consent_token', 'consent_accepted_at', 'assigned_coupon_code', 'acceptance_ip']);
        });
    }
};
