<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('peran')->default('penghuni')->after('email');
            $table->foreignId('kos_id')->nullable()->after('peran')->constrained('kos')->nullOnDelete();
            $table->foreignId('kamar_id')->nullable()->after('kos_id')->constrained('kamar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kamar_id');
            $table->dropConstrainedForeignId('kos_id');
            $table->dropColumn('peran');
        });
    }
};
