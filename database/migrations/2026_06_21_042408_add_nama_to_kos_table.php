<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('kos', 'nama')) {
            return;
        }

        Schema::table('kos', function (Blueprint $table) {
            $table->string('nama')->after('pemilik_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('kos', 'nama')) {
            return;
        }

        Schema::table('kos', function (Blueprint $table) {
            $table->dropColumn('nama');
        });
    }
};
