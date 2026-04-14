<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('driver', 50);
            $table->text('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sms_providers'); }
};