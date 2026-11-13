<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('request_id', 36)->nullable()->index();
            $table->string('result', 16)->default('succeeded');
            $table->string('ip_address', 45)->nullable();

            // The list sorts by time, event, or result, and filters by event or result.
            $table->index('created_at');
            $table->index(['event', 'created_at']);
            $table->index(['result', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['request_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['event', 'created_at']);
            $table->dropIndex(['result', 'created_at']);
            $table->dropColumn(['request_id', 'result', 'ip_address']);
        });
    }
};
