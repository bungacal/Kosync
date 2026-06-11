<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kos_id');
            $table->text('pesan');
            $table->string('target');
            $table->string('tanggal');
            $table->timestamps();

            $table->foreign('kos_id')
                  ->references('id')->on('kos')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast');
    }
};
