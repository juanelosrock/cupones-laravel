<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('locatable_type');   // App\Models\Zone | App\Models\PointOfSale
            $table->unsignedBigInteger('locatable_id');
            $table->timestamps();

            $table->unique(['campaign_id', 'locatable_type', 'locatable_id'], 'campaign_location_unique');
            $table->index(['locatable_type', 'locatable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_locations');
    }
};
