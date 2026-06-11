<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fasilitas_kos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kos_id');
            $table->string('nama');
            $table->timestamps();

            $table->foreign('kos_id')
                  ->references('id')->on('kos')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas_kos');
    }
};
