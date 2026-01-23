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
    Schema::table('pembimbing', function (Blueprint $table) {
        // Menambahkan kolom timestamp yang boleh kosong (nullable)
        $table->timestamp('last_login')->nullable()->after('password');
    });
    }

    public function down(): void
    {
        Schema::table('pembimbing', function (Blueprint $table) {
            $table->dropColumn('last_login');
        });
    }
};
