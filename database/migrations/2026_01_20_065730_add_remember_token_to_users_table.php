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
        // Tambahkan remember_token ke tabel admin
        Schema::table('admin', function (Blueprint $table) {
            $table->rememberToken(); // Ini shorthand untuk $table->string('remember_token', 100)->nullable();
        });

        // Tambahkan remember_token ke tabel pembimbing
        Schema::table('pembimbing', function (Blueprint $table) {
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropRememberToken();
        });

        Schema::table('pembimbing', function (Blueprint $table) {
            $table->dropRememberToken();
        });
    }
};