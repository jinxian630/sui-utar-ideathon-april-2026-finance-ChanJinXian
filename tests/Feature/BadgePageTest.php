<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_earned_badges_link_to_suivision_when_object_url_exists(): void
    {
        $user = User::factory()->create();
        $url = 'https://testnet.suivision.xyz/object/0x' . str_repeat('a', 64);

        Badge::create([
            'user_id' => $user->id,
            'slug' => 'saver',
            'name' => 'Saver - Level 1',
            'threshold' => 100,
            'level' => 1,
            'sui_object_id' => '0x' . str_repeat('a', 64),
            'suivision_url' => $url,
        ]);

        $this->actingAs($user)
            ->get(route('badges'))
            ->assertOk()
            ->assertSee($url, false)
            ->assertSee('View Saver Lv.1 on SuiVision Testnet', false)
            ->assertSee('Share Saver Lv.1 on Facebook', false)
            ->assertSee('https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url), false);
    }
}
