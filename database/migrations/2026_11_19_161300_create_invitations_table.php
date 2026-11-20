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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role_name', 50);
            $table->unsignedBigInteger('invited_by');
            $table->enum('is_enabled', ['Y', 'N'])->default('Y');
            $table->enum('is_deleted', ['Y', 'N'])->default('N');
            $table->timestamps();

            $table->index('role_name');
            $table->index('company_id');
            $table->index('user_id');
            $table->index('invited_by');
            $table->index('is_enabled');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
