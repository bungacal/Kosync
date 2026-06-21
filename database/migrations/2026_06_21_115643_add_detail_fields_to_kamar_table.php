<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->unsignedInteger('harga')->default(0)->after('lantai');
            $table->string('ukuran')->nullable()->after('harga');
            $table->string('tipe')->nullable()->after('ukuran');
            $table->json('fasilitas')->nullable()->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropColumn(['harga', 'ukuran', 'tipe', 'fasilitas']);
        });
    }
};
