<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The name a person gave the storage; empty means the name of its kind ("Amazon S3", "SFTP"). Two storages never share a name. */
    public function up(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->unique()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropColumn('name');
        });
    }
};
