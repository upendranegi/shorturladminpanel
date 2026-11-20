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
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('title', 100)->nullable();
            $table->string('original_url', 2048);
            $table->enum('is_enabled', ['Y', 'N'])->default('Y');
            $table->enum('is_deleted', ['Y', 'N'])->default('N');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('company_id', 'idx_company_id');
            $table->index('is_enabled', 'idx_is_enabled');
            $table->index('is_deleted', 'idx_is_deleted');
            $table->index(['company_id', 'is_deleted'], 'idx_company_id_is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
