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
            $table->unsignedBigInteger('kos_id');
            $table->unsignedBigInteger('kamar_id');
            $table->unsignedBigInteger('penghuni_id');
            $table->string('masalah');
            $table->string('kategori');
            $table->string('status')->default('Pending');
            $table->string('assign')->nullable();
            $table->string('estimasi')->nullable();
            $table->tinyInteger('nilai_rating')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();

            $table->foreign('kos_id')
                  ->references('id')->on('kos')
                  ->cascadeOnDelete();

            $table->foreign('kamar_id')
                  ->references('id')->on('kamar')
                  ->cascadeOnDelete();

            $table->foreign('penghuni_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_perawatan');
    }
};
