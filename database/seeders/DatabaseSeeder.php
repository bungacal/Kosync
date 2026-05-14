<?php

namespace Database\Seeders;

use App\Models\Kos;
use App\Models\Room;
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
        Room::query()->delete();
        Kos::query()->delete();
        User::query()->delete();

        $owner = User::query()->create([
            'name' => 'Sarah Pemilik Kos',
            'email' => 'owner@kosync.test',
            'password' => 'owner123',
            'role' => 'owner',
        ]);

        $kos = Kos::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Kos Kencana',
        ]);

        $owner->update(['kos_id' => $kos->id]);

        $rooms = collect([
            ['number' => '101', 'floor' => 'Lantai 1'],
            ['number' => '102', 'floor' => 'Lantai 1'],
            ['number' => '103', 'floor' => 'Lantai 1'],
            ['number' => '104', 'floor' => 'Lantai 1'],
            ['number' => '201', 'floor' => 'Lantai 2'],
            ['number' => '202', 'floor' => 'Lantai 2'],
            ['number' => '203', 'floor' => 'Lantai 2'],
            ['number' => '204', 'floor' => 'Lantai 2'],
        ])->map(fn (array $room) => Room::query()->create([
            'kos_id' => $kos->id,
            'number' => $room['number'],
            'floor' => $room['floor'],
            'status' => 'Kosong',
        ]))->keyBy('number');

        $tenant = User::query()->create([
            'name' => 'Nama Penghuni Kos',
            'email' => 'penghuni@kosync.test',
            'password' => 'penghuni123',
            'role' => 'tenant',
            'kos_id' => $kos->id,
            'room_id' => $rooms->get('101')->id,
        ]);

        $rooms->get('101')->update([
            'tenant_id' => $tenant->id,
            'status' => 'Terisi',
        ]);
    }
}
