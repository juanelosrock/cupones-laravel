<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sms_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->enum('status', ['pending', 'sent', 'failed', 'unsubscribed'])->default('pending');
            $table->text('message_sent')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->text('provider_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['sms_campaign_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('sms_recipients'); }
};