<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 10)->nullable();
            $table->string('document_number', 30)->nullable()->unique();
            $table->string('name', 100);
            $table->string('lastname', 100)->nullable();
            $table->string('email', 150)->nullable()->unique();
            $table->string('phone', 20)->unique();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'O', 'N'])->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->enum('status', ['active', 'blocked', 'unsubscribed'])->default('active');
            $table->enum('created_via', ['api', 'web', 'sms', 'import', 'manual'])->default('api');
            $table->boolean('data_treatment_accepted')->default(false);
            $table->timestamp('data_treatment_accepted_at')->nullable();
            $table->string('acceptance_ip', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['phone', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};