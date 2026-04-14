<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sms_opt_outs', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('reason', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('sms_opt_outs'); }
};