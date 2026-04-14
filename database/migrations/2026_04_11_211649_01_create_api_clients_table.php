<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('client_id', 100)->unique();
            $table->string('client_secret', 255);
            $table->json('allowed_ips')->nullable();
            $table->unsignedInteger('rate_limit_per_minute')->default(30);
            $table->json('permissions')->nullable();
            $table->enum('status', ['active', 'inactive', 'revoked'])->default('active');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('api_clients'); }
};