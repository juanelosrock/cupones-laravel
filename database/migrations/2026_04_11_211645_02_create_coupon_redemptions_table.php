<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_reference', 100)->nullable();
            $table->decimal('original_amount', 12, 2);
            $table->decimal('discount_applied', 12, 2);
            $table->decimal('final_amount', 12, 2);
            $table->enum('channel', ['api', 'web', 'manual', 'sms'])->default('api');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('redeemed_at')->useCurrent();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['coupon_id', 'customer_id']);
            $table->index('redeemed_at');
        });
    }
    public function down(): void { Schema::dropIfExists('coupon_redemptions'); }
};