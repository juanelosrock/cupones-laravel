<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupon_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('code_type', ['unique', 'general'])->default('unique');
            $table->string('general_code', 50)->nullable()->unique();
            $table->string('prefix', 20)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_purchase_amount', 12, 2)->default(0);
            $table->decimal('max_purchase_amount', 12, 2)->nullable();
            $table->unsignedInteger('max_uses_total')->nullable();
            $table->unsignedInteger('max_uses_per_user')->nullable();
            $table->unsignedInteger('max_uses_per_day')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_combinable')->default(false);
            $table->enum('applicable_to', ['all', 'product', 'category', 'pos', 'zone', 'city'])->default('all');
            $table->enum('status', ['draft', 'active', 'paused', 'expired', 'cancelled'])->default('draft');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('coupon_batches'); }
};