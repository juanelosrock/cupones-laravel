<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_client_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('email_subject', 255)->nullable();
            $table->enum('sms_status',   ['sent', 'failed', 'skipped'])->nullable();
            $table->enum('email_status', ['sent', 'failed', 'skipped'])->nullable();
            $table->string('sms_message_id',   100)->nullable();
            $table->string('email_message_id', 100)->nullable();
            $table->text('sms_error')->nullable();
            $table->text('email_error')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['api_client_id', 'created_at']);
            $table->index('phone');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
