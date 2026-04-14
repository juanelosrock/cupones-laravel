<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('coupon_batches')->cascadeOnDelete();
            $table->string('code', 50)->unique();
            $table->enum('status', ['active', 'used', 'expired', 'cancelled'])->default('active');
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['code', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('coupons'); }
};