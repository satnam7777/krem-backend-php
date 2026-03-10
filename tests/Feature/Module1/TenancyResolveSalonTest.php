<?php

namespace Tests\Feature\Module1;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenancyResolveSalonTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_salon_blocks_non_member(): void
    {
        $user = User::factory()->create();
        $salon = Salon::factory()->create();

        Sanctum::actingAs($user);

        $this->withHeader('X-Salon-Id', $salon->id)
            ->getJson('/api/salons/current')
            ->assertStatus(403);
    }
}
