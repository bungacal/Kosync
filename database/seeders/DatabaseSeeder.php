<?php

namespace Database\Seeders;

use App\Models\Broadcast;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\LaporanPerawatan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Broadcast::query()->delete();
        Pesan::query()->delete();
        LaporanPerawatan::query()->delete();
        Kamar::query()->delete();
        Kos::query()->delete();
        User::query()->delete();

        $pemilik = User::query()->create([
            'name' => 'Sarah Pemilik Kos',
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
            'peran' => 'pemilik',
        ]);

        $kos = Kos::query()->create([
            'pemilik_id' => $pemilik->id,
            'nama' => 'Kos Kencana',
        ]);

        $pemilik->update(['kos_id' => $kos->id]);

        $kamar = collect([
            ['nomor' => '101', 'lantai' => 'Lantai 1'],
            ['nomor' => '102', 'lantai' => 'Lantai 1'],
            ['nomor' => '103', 'lantai' => 'Lantai 1'],
            ['nomor' => '104', 'lantai' => 'Lantai 1'],
            ['nomor' => '201', 'lantai' => 'Lantai 2'],
            ['nomor' => '202', 'lantai' => 'Lantai 2'],
            ['nomor' => '203', 'lantai' => 'Lantai 2'],
            ['nomor' => '204', 'lantai' => 'Lantai 2'],
        ])->map(fn (array $kamar) => Kamar::query()->create([
            'kos_id' => $kos->id,
            'nomor' => $kamar['nomor'],
            'lantai' => $kamar['lantai'],
            'status' => 'Kosong',
        ]))->keyBy('nomor');

        $penghuni = User::query()->create([
            'name' => 'Nama Penghuni Kos',
            'email' => 'user@gmail.com',
            'password' => 'admin123',
            'peran' => 'penghuni',
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->get('101')->id,
        ]);

        $kamar->get('101')->update([
            'penghuni_id' => $penghuni->id,
            'status' => 'Terisi',
        ]);

        LaporanPerawatan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->get('101')->id,
            'masalah' => 'Pintu kamar sudah diperbaiki',
            'kategori' => 'Pintu',
            'status' => 'Selesai',
            'assign' => 'Pak Heru',
            'estimasi' => 'Selesai kemarin',
            'selesai_pada' => now()->subDay(),
        ]);

        LaporanPerawatan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->get('101')->id,
            'masalah' => 'Lampu kamar mati sejak pagi',
            'kategori' => 'Listrik',
            'status' => 'Diproses',
            'assign' => 'Teknisi listrik',
            'estimasi' => 'Besok pagi',
        ]);

        Pesan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $penghuni->id,
            'pengirim' => 'owner',
            'pesan' => 'Halo, laporan lampu sudah kami terima ya.',
        ]);

        Pesan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $penghuni->id,
            'pengirim' => 'penghuni',
            'pesan' => 'Terima kasih, kira-kira kapan bisa diperbaiki?',
        ]);

        Broadcast::query()->create([
            'kos_id' => $kos->id,
            'pesan' => 'Air akan mati sementara pukul 10.00 sampai 12.00 karena perbaikan pipa.',
            'target' => 'Semua Penghuni',
            'tanggal' => now()->translatedFormat('d F'),
        ]);
    }
}
