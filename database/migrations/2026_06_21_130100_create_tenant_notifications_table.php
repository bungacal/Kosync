<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_penghuni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('laporan_perawatan_id')->nullable()->constrained('laporan_perawatan')->cascadeOnDelete();
            $table->foreignId('broadcast_id')->nullable()->constrained('broadcast')->cascadeOnDelete();
            $table->string('tipe');
            $table->string('ikon')->default('bell');
            $table->string('warna')->default('rose');
            $table->string('judul');
            $table->text('isi');
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_penghuni');
    }
};
