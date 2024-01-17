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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->string('meta_description');
            $table->string('meta_keyword');
            $table->string('meta_image')->nullable();
            $table->string('favicon')->nullable();
            $table->string('logo')->nullable();
            $table->string('footer_left');
            $table->string('footer_right');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
