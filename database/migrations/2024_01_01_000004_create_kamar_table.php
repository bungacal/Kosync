<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kos_id');
            $table->unsignedBigInteger('tipe_kamar_id');
            $table->unsignedBigInteger('penghuni_id')->nullable();
            $table->string('nomor', 50);
            $table->string('lantai', 20)->nullable();
            $table->string('status', 50)->default('Kosong');
            $table->timestamps();

            $table->unique(['kos_id', 'nomor']);

            $table->foreign('kos_id')
                  ->references('id')->on('kos')
                  ->cascadeOnDelete();

            $table->foreign('tipe_kamar_id')
                  ->references('id')->on('tipe_kamar')
                  ->cascadeOnDelete();

            $table->foreign('penghuni_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};

