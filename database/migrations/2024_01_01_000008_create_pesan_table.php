<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kos_id');
            $table->unsignedBigInteger('penghuni_id');
            $table->string('pengirim');
            $table->text('pesan');
            $table->timestamps();

            $table->foreign('kos_id')
                  ->references('id')->on('kos')
                  ->cascadeOnDelete();

            $table->foreign('penghuni_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan');
    }
};
