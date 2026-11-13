<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per kind of storage. `settings` holds the connection values encrypted; it is empty while the defaults of the server
     * environment apply. The result of the last connection test sits beside it.
     */
    public function up(): void
    {
        Schema::create('storage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->unique();
            $table->text('settings')->nullable();
            $table->timestamp('tested_at')->nullable();
            $table->boolean('test_ok')->nullable();
            $table->string('test_message', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_settings');
    }
};
