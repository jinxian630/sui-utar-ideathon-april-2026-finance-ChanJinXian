module finance_tracker::finance_profile {
    public struct FinanceProfile has key, store {
        id: UID,
        owner: address,
        total_saved: u64,
        entry_count: u64,
        created_at: u64,
    }
 
    public entry fun create_profile(ctx: &mut TxContext) {
        let profile = FinanceProfile {
            id: object::new(ctx), owner: tx_context::sender(ctx),
            total_saved: 0, entry_count: 0,
            created_at: tx_context::epoch(ctx),
        };
        transfer::transfer(profile, tx_context::sender(ctx));
    }
 
    public entry fun add_savings(
        profile: &mut FinanceProfile, amount: u64, _ctx: &mut TxContext
    ) {
        assert!(amount > 0, 0);
        profile.total_saved = profile.total_saved + amount;
        profile.entry_count = profile.entry_count + 1;
    }
}
