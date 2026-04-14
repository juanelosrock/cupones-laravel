<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('description');
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['type', 'severity']);
        });
    }
    public function down(): void { Schema::dropIfExists('security_alerts'); }
};