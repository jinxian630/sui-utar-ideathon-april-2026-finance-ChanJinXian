/// finance_tracker::loyalty_badge
/// Handles milestone badge minting AND on-chain rebate calculation/emission.
/// The rebate formula: rebate = goal_target * (base_rate + tier_bonus)
/// Tier table (base=1%, bonus increases per tier):
///   Level 1 (Saver):          threshold=100,   bonus=0%   → total 1%
///   Level 2 (Investor):       threshold=500,   bonus=0.5% → total 1.5%
///   Level 3 (Wealth Builder): threshold=1000,  bonus=1%   → total 2%
///   Level 4 (Diamond Saver):  threshold=5000,  bonus=2%   → total 3%
///   Level 5 (Finance Master): threshold=10000, bonus=4%   → total 5%
module finance_tracker::loyalty_badge {
    use std::string::{Self, String};

    // ── Structs ──────────────────────────────────────────────────────────────

    /// Non-transferable achievement badge NFT
    public struct LoyaltyBadge has key {
        id: UID,
        recipient: address,
        tier_slug: String,       // e.g. "saver", "investor"
        tier_level: u64,         // 1–5
        threshold: u64,          // milestone in RM cents (e.g. 10000 = RM 100)
        rebate_mist: u64,        // rebate issued in MIST (1 SUI = 1_000_000_000 MIST)
        stacked: bool,           // true if 1.2× stacking multiplier was applied
        name: String,
        issued_epoch: u64,
    }

    /// Event emitted on every badge + rebate claim — useful for indexing
    public struct RebateIssued has copy, drop {
        recipient: address,
        tier_slug: String,
        rebate_mist: u64,
        stacked: bool,
        epoch: u64,
    }

    /// Event emitted when a badge NFT is minted for a user.
    public struct BadgeEarned has copy, drop {
        user_address: address,
        badge_level: u64,
        tier_slug: String,
        badge_object_id: address,
    }

    // ── Constants (amounts in RM cents; 1 RM = 100 cents) ──────────────────
    const BASE_RATE_BPS: u64 = 100;   // 1.00% in basis points (1 bps = 0.01%)

    // ── Entry Functions ──────────────────────────────────────────────────────

    /// Mint a milestone badge and emit a rebate event.
    /// Called by the server-side SuiService PTB.
    /// `goal_target_cents` is the goal target in RM × 100 (e.g. RM 500 = 50000).
    /// `tier_bonus_bps` is the bonus in basis points for this tier.
    /// `is_stacked` is true if the 1.2× multiplier applies.
    public entry fun claim_milestone_badge(
        recipient: address,
        tier_slug: vector<u8>,
        tier_level: u64,
        threshold_cents: u64,
        tier_bonus_bps: u64,
        is_stacked: bool,
        ctx: &mut TxContext
    ) {
        let epoch = tx_context::epoch(ctx);

        // Rebate = goal_target × (base_rate + tier_bonus) / 10000 (in cents)
        let total_rate_bps = BASE_RATE_BPS + tier_bonus_bps;
        let rebate_cents   = (threshold_cents * total_rate_bps) / 10_000;

        // Apply stacking multiplier: ×1.2 = multiply by 6 then divide by 5
        let final_rebate_cents = if (is_stacked) {
            (rebate_cents * 6) / 5
        } else {
            rebate_cents
        };

        // Convert RM cents to MIST (1 RM = 1_000_000_000 MIST / 100)
        let rebate_mist = final_rebate_cents * 10_000_000;

        let slug_str = string::utf8(tier_slug);

        // Mint the badge NFT and transfer to recipient
        let badge = LoyaltyBadge {
            id: object::new(ctx),
            recipient,
            tier_slug: slug_str,
            tier_level,
            threshold: threshold_cents,
            rebate_mist,
            stacked: is_stacked,
            name: string::utf8(b"Finance Tracker Milestone Badge"),
            issued_epoch: epoch,
        };
        let badge_object_id = object::uid_to_address(&badge.id);

        sui::event::emit(BadgeEarned {
            user_address: recipient,
            badge_level: tier_level,
            tier_slug: slug_str,
            badge_object_id,
        });

        transfer::transfer(badge, recipient);

        // Emit event for indexer / backend to read the rebate amount
        sui::event::emit(RebateIssued {
            recipient,
            tier_slug: slug_str,
            rebate_mist,
            stacked: is_stacked,
            epoch,
        });
    }

    /// Legacy entry kept for backward compatibility
    public entry fun mint_badge(
        recipient: address, streak_days: u64, ctx: &mut TxContext
    ) {
        let badge = LoyaltyBadge {
            id: object::new(ctx),
            recipient,
            tier_slug: string::utf8(b"legacy"),
            tier_level: 0,
            threshold: 0,
            rebate_mist: 0,
            stacked: false,
            name: string::utf8(b"30-Day Round-Up Loyalty Badge"),
            issued_epoch: tx_context::epoch(ctx),
        };
        transfer::transfer(badge, recipient);
    }
}
