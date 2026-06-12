<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\LaporanPerawatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenghuniReportFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_home_shows_only_their_active_reports(): void
    {
        [$tenant, $room, $kos] = $this->tenantWithRoom();

        LaporanPerawatan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $tenant->id,
            'kamar_id' => $room->id,
            'masalah' => 'Lampu kamar mati',
            'kategori' => 'Listrik',
            'status' => 'Pending',
        ]);

        $this->actingAs($tenant)
            ->get(route('penghuni.home'))
            ->assertOk()
            ->assertSee('Lampu kamar mati')
            ->assertSee('Laporan Aktif');
    }

    public function test_tenant_can_create_report_for_their_own_room(): void
    {
        [$tenant, $room, $kos] = $this->tenantWithRoom();

        $response = $this->actingAs($tenant)
            ->from(route('penghuni.reports'))
            ->post(route('penghuni.reports.store'), [
                'kategori' => 'Air',
                'masalah' => 'Keran kamar mandi bocor',
            ]);

        $response
            ->assertRedirect(route('penghuni.reports'))
            ->assertSessionHas('status', 'Laporan berhasil dibuat.');

        $this->assertDatabaseHas('laporan_perawatan', [
            'kos_id' => $kos->id,
            'penghuni_id' => $tenant->id,
            'kamar_id' => $room->id,
            'kategori' => 'Air',
            'masalah' => 'Keran kamar mandi bocor',
            'status' => 'Pending',
        ]);
    }

    public function test_report_category_must_be_valid(): void
    {
        [$tenant] = $this->tenantWithRoom();

        $this->actingAs($tenant)
            ->from(route('penghuni.reports'))
            ->post(route('penghuni.reports.store'), [
                'kategori' => 'Kategori Bebas',
                'masalah' => 'Masalah test',
            ])
            ->assertRedirect(route('penghuni.reports'))
            ->assertSessionHasErrors('kategori');

        $this->assertDatabaseCount('laporan_perawatan', 0);
    }

    public function test_history_only_shows_completed_reports(): void
    {
        [$tenant, $room, $kos] = $this->tenantWithRoom();

        LaporanPerawatan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $tenant->id,
            'kamar_id' => $room->id,
            'masalah' => 'Pintu sudah diperbaiki',
            'kategori' => 'Pintu',
            'status' => 'Selesai',
            'selesai_pada' => now(),
        ]);

        LaporanPerawatan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $tenant->id,
            'kamar_id' => $room->id,
            'masalah' => 'AC masih dicek',
            'kategori' => 'AC',
            'status' => 'Diproses',
        ]);

        $this->actingAs($tenant)
            ->get(route('penghuni.history'))
            ->assertOk()
            ->assertSee('Pintu sudah diperbaiki')
            ->assertDontSee('AC masih dicek');
    }

    public function test_tenant_can_rate_their_completed_report_once(): void
    {
        [$tenant, $room, $kos] = $this->tenantWithRoom();

        $report = LaporanPerawatan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $tenant->id,
            'kamar_id' => $room->id,
            'masalah' => 'Pintu sudah diperbaiki',
            'kategori' => 'Pintu',
            'status' => 'Selesai',
            'selesai_pada' => now(),
        ]);

        $this->actingAs($tenant)
            ->post(route('penghuni.laporan.rating', $report), [
                'nilai_rating' => 5,
            ])
            ->assertRedirect(route('penghuni.history'))
            ->assertSessionHas('status', 'Rating berhasil dikirim.');

        $this->assertSame(5, $report->fresh()->nilai_rating);

        $this->actingAs($tenant)
            ->post(route('penghuni.laporan.rating', $report), [
                'nilai_rating' => 3,
            ])
            ->assertSessionHasErrors('nilai_rating');

        $this->assertSame(5, $report->fresh()->nilai_rating);
    }

    public function test_tenant_cannot_rate_another_tenants_report(): void
    {
        [$tenant] = $this->tenantWithRoom();
        [$otherTenant, $otherRoom, $otherKos] = $this->tenantWithRoom(
            'other-owner@example.com',
            'other-tenant@example.com',
            'Kos Lain',
            '201'
        );

        $report = LaporanPerawatan::query()->create([
            'kos_id' => $otherKos->id,
            'penghuni_id' => $otherTenant->id,
            'kamar_id' => $otherRoom->id,
            'masalah' => 'Laporan penghuni lain',
            'kategori' => 'Listrik',
            'status' => 'Selesai',
            'selesai_pada' => now(),
        ]);

        $this->actingAs($tenant)
            ->post(route('penghuni.laporan.rating', $report), [
                'nilai_rating' => 5,
            ])
            ->assertForbidden();

        $this->assertNull($report->fresh()->nilai_rating);
    }

    private function tenantWithRoom(
        string $ownerEmail = 'owner@example.com',
        string $tenantEmail = 'tenant@example.com',
        string $kosName = 'Kos Test',
        string $roomNumber = '101'
    ): array {
        $owner = User::query()->create([
            'name' => 'Owner Test',
            'email' => $ownerEmail,
            'password' => 'secret123',
            'peran' => 'pemilik',
        ]);

        $kos = Kos::query()->create([
            'pemilik_id' => $owner->id,
            'nama' => $kosName,
        ]);

        $owner->update(['kos_id' => $kos->id]);

        $room = Kamar::query()->create([
            'kos_id' => $kos->id,
            'nomor' => $roomNumber,
            'lantai' => 'Lantai 1',
            'status' => 'Terisi',
        ]);

        $tenant = User::query()->create([
            'name' => 'Tenant Test',
            'email' => $tenantEmail,
            'password' => 'secret123',
            'peran' => 'penghuni',
            'kos_id' => $kos->id,
            'kamar_id' => $room->id,
        ]);

        $room->update(['penghuni_id' => $tenant->id]);

        return [$tenant, $room, $kos];
    }
}
