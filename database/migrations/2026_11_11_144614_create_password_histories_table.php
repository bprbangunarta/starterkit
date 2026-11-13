<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'id']);
        });

        // The password each account has now is the first one in its history.
        DB::table('password_histories')->insertUsing(
            ['user_id', 'password', 'created_at'],
            DB::table('users')->select('id', 'password', DB::raw('current_timestamp')),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');
    }
};
