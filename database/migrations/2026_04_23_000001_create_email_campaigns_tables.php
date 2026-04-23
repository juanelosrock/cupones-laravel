<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject', 255);
            $table->string('from_name', 100)->default('CuponesHub');
            $table->string('from_email', 150);
            $table->text('message_template');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 150);
            $table->enum('status', ['pending', 'sent', 'failed', 'unsubscribed'])->default('pending');
            $table->text('subject_sent')->nullable();
            $table->text('message_sent')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('assigned_coupon_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->text('provider_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['email_campaign_id', 'status']);
        });

        Schema::create('email_opt_outs', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->unique();
            $table->string('reason', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_opt_outs');
        Schema::dropIfExists('email_recipients');
        Schema::dropIfExists('email_campaigns');
    }
};
