<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('endpoint', 200);
            $table->string('method', 10);
            $table->string('request_hash', 64)->nullable();
            $table->json('request_body')->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->unsignedInteger('processing_ms')->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['api_client_id', 'created_at']);
            $table->index('request_hash');
        });
    }
    public function down(): void { Schema::dropIfExists('api_request_logs'); }
};