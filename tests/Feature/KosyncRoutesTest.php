<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KosyncRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_feature_pages_render(): void
    {
        $this->seed();
        $owner = User::query()->where('peran', 'pemilik')->firstOrFail();

        $this->actingAs($owner)->get(route('pemilik.home'))->assertOk();
        $this->actingAs($owner)->get(route('pemilik.laporan'))->assertOk();
        $this->actingAs($owner)->get(route('pemilik.komunikasi'))->assertOk();
    }

    public function test_tenant_message_page_renders(): void
    {
        $this->seed();
        $tenant = User::query()->where('peran', 'penghuni')->firstOrFail();

        $this->actingAs($tenant)->get(route('penghuni.messages'))->assertOk();
    }
}
