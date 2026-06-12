<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_sign_up_and_is_redirected_to_owner_dashboard(): void
    {
        $response = $this->post(route('register.pemilik.store'), [
            'name' => 'Ayu Pemilik',
            'email' => 'AYU@example.com',
            'password' => 'secret123',
            'kos_name' => 'Kos Melati',
        ]);

        $owner = User::query()->where('email', 'ayu@example.com')->firstOrFail();
        $kos = Kos::query()->where('nama', 'Kos Melati')->firstOrFail();

        $response->assertRedirect(route('pemilik.home'));
        $this->assertAuthenticatedAs($owner);
        $this->assertSame('pemilik', $owner->peran);
        $this->assertTrue(Hash::check('secret123', $owner->password));
        $this->assertTrue($owner->is($kos->pemilik));
        $this->assertTrue($kos->is($owner->kos));
    }

    public function test_tenant_can_sign_up_with_an_available_room(): void
    {
        [$kos, $room] = $this->availableRoom();

        $response = $this->post(route('register.penghuni.store'), [
            'name' => 'Bima Penghuni',
            'email' => 'BIMA@example.com',
            'password' => 'secret123',
            'kos_id' => $kos->id,
            'kamar_id' => $room->id,
        ]);

        $tenant = User::query()->where('email', 'bima@example.com')->firstOrFail();

        $response->assertRedirect(route('penghuni.home'));
        $this->assertAuthenticatedAs($tenant);
        $this->assertSame('penghuni', $tenant->peran);
        $this->assertTrue($tenant->kos->is($kos));
        $this->assertTrue($tenant->kamar->is($room));
        $this->assertSame('Terisi', $room->fresh()->status);
        $this->assertTrue($tenant->is($room->fresh()->penghuni));
    }

    public function test_tenant_cannot_sign_up_with_an_unavailable_room(): void
    {
        [$kos, $room] = $this->availableRoom();
        $room->update(['status' => 'Terisi']);

        $response = $this
            ->from(route('register.penghuni'))
            ->post(route('register.penghuni.store'), [
                'name' => 'Citra Penghuni',
                'email' => 'citra@example.com',
                'password' => 'secret123',
                'kos_id' => $kos->id,
                'kamar_id' => $room->id,
            ]);

        $response
            ->assertRedirect(route('register.penghuni'))
            ->assertSessionHasErrors('kamar_id');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'citra@example.com']);
    }

    public function test_users_login_to_their_role_dashboard(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();

        $this->post(route('login.store'), [
            'email' => $owner->email,
            'password' => 'secret123',
            'peran' => 'pemilik',
        ])->assertRedirect(route('pemilik.home'));

        $this->assertAuthenticatedAs($owner);
        auth()->logout();

        $this->post(route('login.store'), [
            'email' => $tenant->email,
            'password' => 'secret123',
            'peran' => 'penghuni',
        ])->assertRedirect(route('penghuni.home'));

        $this->assertAuthenticatedAs($tenant);
    }

    public function test_login_rejects_a_valid_account_with_the_wrong_role(): void
    {
        [$owner] = $this->ownerAndTenant();

        $response = $this
            ->from(route('login'))
            ->post(route('login.store'), [
                'email' => $owner->email,
                'password' => 'secret123',
                'peran' => 'penghuni',
            ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function availableRoom(): array
    {
        $owner = User::query()->create([
            'name' => 'Owner Test',
            'email' => 'owner-test@example.com',
            'password' => 'secret123',
            'peran' => 'pemilik',
        ]);

        $kos = Kos::query()->create([
            'pemilik_id' => $owner->id,
            'nama' => 'Kos Test',
        ]);

        $owner->update(['kos_id' => $kos->id]);

        $room = Kamar::query()->create([
            'kos_id' => $kos->id,
            'nomor' => '101',
            'lantai' => 'Lantai 1',
            'status' => 'Kosong',
        ]);

        return [$kos, $room];
    }

    private function ownerAndTenant(): array
    {
        [$kos, $room] = $this->availableRoom();
        $owner = $kos->pemilik;

        $tenant = User::query()->create([
            'name' => 'Tenant Test',
            'email' => 'tenant-test@example.com',
            'password' => 'secret123',
            'peran' => 'penghuni',
            'kos_id' => $kos->id,
            'kamar_id' => $room->id,
        ]);

        $room->update([
            'penghuni_id' => $tenant->id,
            'status' => 'Terisi',
        ]);

        return [$owner, $tenant];
    }
}
