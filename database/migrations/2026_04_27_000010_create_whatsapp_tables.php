<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message_template');
            $table->json('filters')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->enum('status', ['draft','scheduled','sending','sent','failed','cancelled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('whatsapp_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->string('assigned_coupon_code', 50)->nullable();
            $table->enum('status', ['pending','sent','failed','unsubscribed'])->default('pending');
            $table->text('message_sent')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->text('provider_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['whatsapp_campaign_id', 'status']);
        });

        Schema::create('whatsapp_opt_outs', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('reason', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_recipients');
        Schema::dropIfExists('whatsapp_campaigns');
        Schema::dropIfExists('whatsapp_opt_outs');
    }
};
