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
        Schema::create('campaign_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('source', 30)->default('import'); // import | manual
            $table->string('import_batch', 50)->nullable();  // UUID del lote de importación
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['campaign_id', 'customer_id']);
            $table->index(['campaign_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_customers');
    }
};
