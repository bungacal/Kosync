<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_perawatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->foreignId('penghuni_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamar')->cascadeOnDelete();
            $table->string('masalah');
            $table->string('kategori');
            $table->string('status')->default('Pending');
            $table->unsignedTinyInteger('nilai_rating')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_perawatan');
    }
};
