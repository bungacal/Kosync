<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_perawatan', function (Blueprint $table) {
            $table->text('ulasan_rating')->nullable()->after('nilai_rating');
            $table->timestamp('rated_at')->nullable()->after('ulasan_rating');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_perawatan', function (Blueprint $table) {
            $table->dropColumn(['ulasan_rating', 'rated_at']);
        });
    }
};
