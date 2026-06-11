<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('peran')->default('penghuni');
            $table->unsignedBigInteger('kos_id')->nullable();
            $table->unsignedBigInteger('kamar_id')->nullable();
            $table->string('password');
            $table->timestamps();

            $table->foreign('kos_id')
                  ->references('id')->on('kos')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};