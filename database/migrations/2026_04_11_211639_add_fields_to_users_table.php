<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('document_type', 10)->nullable()->after('phone');
            $table->string('document_number', 30)->nullable()->unique()->after('document_type');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->after('document_number');
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'document_type', 'document_number', 'status', 'deleted_at']);
        });
    }
};