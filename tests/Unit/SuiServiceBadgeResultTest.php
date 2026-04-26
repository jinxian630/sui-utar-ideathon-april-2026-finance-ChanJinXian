<?php

namespace Tests\Unit;

use App\Services\SuiService;
use Tests\TestCase;

class SuiServiceBadgeResultTest extends TestCase
{
    public function test_summarize_badge_mint_result_prefers_badge_earned_event(): void
    {
        $service = new SuiService();
        $objectId = '0x' . str_repeat('c', 64);

        $summary = $service->summarizeBadgeMintResult([
            'digest' => '7BadgeDigest222222222222222222222222222222222222222',
            'events' => [[
                'type' => '0x123::loyalty_badge::BadgeEarned',
                'parsedJson' => [
                    'user_address' => '0x' . str_repeat('a', 64),
                    'badge_level' => '3',
                    'tier_slug' => 'wealth_builder',
                    'badge_object_id' => strtoupper($objectId),
                ],
            ]],
            'objectChanges' => [[
                'type' => 'created',
                'objectType' => '0x123::loyalty_badge::LoyaltyBadge',
                'objectId' => '0x' . str_repeat('d', 64),
            ]],
        ]);

        $this->assertSame('7BadgeDigest222222222222222222222222222222222222222', $summary['digest']);
        $this->assertSame($objectId, $summary['badge_object_id']);
        $this->assertSame(3, $summary['badge_level']);
        $this->assertSame('https://testnet.suivision.xyz/object/' . $objectId, $summary['suivision_url']);
    }

    public function test_summarize_badge_mint_result_falls_back_to_created_object(): void
    {
        $service = new SuiService();
        $objectId = '0x' . str_repeat('e', 64);

        $summary = $service->summarizeBadgeMintResult([
            'effects' => [
                'transactionDigest' => '8BadgeDigest333333333333333333333333333333333333333',
            ],
            'objectChanges' => [[
                'type' => 'created',
                'objectType' => '0x123::loyalty_badge::LoyaltyBadge',
                'objectId' => $objectId,
            ]],
        ], 4);

        $this->assertSame('8BadgeDigest333333333333333333333333333333333333333', $summary['digest']);
        $this->assertSame($objectId, $summary['badge_object_id']);
        $this->assertSame(4, $summary['badge_level']);
        $this->assertSame('https://testnet.suivision.xyz/object/' . $objectId, $summary['suivision_url']);
    }
}
