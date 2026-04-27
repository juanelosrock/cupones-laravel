<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupon_batches', function (Blueprint $table) {
            $table->decimal('max_discount_amount', 12, 2)->nullable()->after('max_purchase_amount');
        });
    }

    public function down(): void
    {
        Schema::table('coupon_batches', function (Blueprint $table) {
            $table->dropColumn('max_discount_amount');
        });
    }
};
