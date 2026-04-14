<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['privacy', 'terms', 'sms_consent', 'commercial']);
            $table->string('title', 200);
            $table->longText('content');
            $table->string('version', 20);
            $table->boolean('is_active')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['type', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('legal_documents'); }
};