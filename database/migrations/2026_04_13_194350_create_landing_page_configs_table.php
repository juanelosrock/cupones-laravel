<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landing_page_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('template', ['minimal', 'branded', 'hero'])->default('minimal');

            // Branding
            $table->string('logo_url', 500)->nullable();
            $table->string('hero_image_url', 500)->nullable();
            $table->string('brand_color', 20)->default('#2563eb');
            $table->string('bg_color', 20)->default('#f1f5f9');

            // Texts — consent form
            $table->string('heading', 200)->default('Autorización de datos personales');
            $table->string('subheading', 500)->nullable();
            $table->text('body_html')->nullable();
            $table->string('button_text', 100)->default('Aceptar y ver mi código');

            // Texts — success screen
            $table->string('success_heading', 200)->default('¡Autorización registrada!');
            $table->string('success_text', 500)->default('Tu consentimiento fue guardado correctamente.');

            // Footer
            $table->string('footer_text', 300)->nullable();

            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_configs');
    }
};
