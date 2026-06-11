<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipe_kamar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kos_id');
            $table->string('nama', 100);
            $table->json('fasilitas')->nullable()->comment('Array fasilitas kamar');
            $table->timestamps();

            $table->foreign('kos_id')
                  ->references('id')->on('kos')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipe_kamar');
    }
};