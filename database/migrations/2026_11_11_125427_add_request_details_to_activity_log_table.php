<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('method', 10)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('user_agent', 255)->nullable();

            // The list sorts by host (the IP address).
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['ip_address']);
            $table->dropColumn(['method', 'path', 'user_agent']);
        });
    }
};
