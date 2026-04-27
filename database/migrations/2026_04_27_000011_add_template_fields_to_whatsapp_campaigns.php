<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_campaigns', function (Blueprint $table) {
            $table->enum('content_type', ['text', 'template'])->default('text')->after('message_template');
            $table->string('template_id', 100)->nullable()->after('content_type');
            $table->json('template_fields')->nullable()->after('template_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_campaigns', function (Blueprint $table) {
            $table->dropColumn(['content_type', 'template_id', 'template_fields']);
        });
    }
};
