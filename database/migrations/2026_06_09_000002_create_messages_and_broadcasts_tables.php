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
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->foreignId('penghuni_id')->constrained('users')->cascadeOnDelete();
            $table->string('pengirim')->default('penghuni');
            $table->text('pesan');
            $table->timestamps();

            $table->index(['kos_id', 'penghuni_id', 'created_at']);
        });

        Schema::create('broadcast', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->text('pesan');
            $table->string('target')->default('Semua Penghuni');
            $table->string('tanggal');
            $table->timestamps();

            $table->index(['kos_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast');
        Schema::dropIfExists('pesan');
    }
};
