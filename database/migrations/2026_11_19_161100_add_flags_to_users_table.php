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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->enum('is_enabled', ['Y', 'N'])->default('Y')->after('password');
            $table->enum('is_deleted', ['Y', 'N'])->default('N')->after('is_enabled');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_deleted');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

            $table->index('company_id');
            $table->index('is_enabled');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropIndex(['is_enabled']);
            $table->dropIndex(['is_deleted']);
            $table->dropColumn(['company_id', 'is_enabled', 'is_deleted', 'created_by', 'updated_by']);
        });
    }
};
