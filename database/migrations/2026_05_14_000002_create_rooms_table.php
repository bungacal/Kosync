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
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->foreignId('penghuni_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nomor');
            $table->string('lantai');
            $table->string('status')->default('Kosong');
            $table->timestamps();

            $table->unique(['kos_id', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
