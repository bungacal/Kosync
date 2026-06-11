<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah foreign key kamar_id ke tabel users
        // (dibuat setelah tabel kamar ada)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('kamar_id')
                  ->references('id')->on('kamar')
                  ->nullOnDelete();
        });

        // Tambah foreign key pemilik_id ke tabel kos
        // (dibuat setelah tabel users ada)
        Schema::table('kos', function (Blueprint $table) {
            $table->foreign('pemilik_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kamar_id']);
        });

        Schema::table('kos', function (Blueprint $table) {
            $table->dropForeign(['pemilik_id']);
        });
    }
};
