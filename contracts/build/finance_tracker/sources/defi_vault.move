module finance_tracker::defi_vault {
    use sui::object::{Self, UID};
    use sui::transfer;
    use sui::tx_context::{Self, TxContext};

    public struct Vault has key {
        id: UID,
        total_staked: u64,
    }

    fun init(ctx: &mut TxContext) {
        let vault = Vault {
            id: object::new(ctx),
            total_staked: 0,
        };
        transfer::share_object(vault);
    }

    public entry fun deposit(vault: &mut Vault, amount: u64, _ctx: &mut TxContext) {
        vault.total_staked = vault.total_staked + amount;
    }
}
