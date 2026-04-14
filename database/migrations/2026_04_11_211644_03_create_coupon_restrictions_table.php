<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupon_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('coupon_batches')->cascadeOnDelete();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['batch_id', 'entity_type', 'entity_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('coupon_restrictions'); }
};