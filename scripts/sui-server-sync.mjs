import { SuiJsonRpcClient } from '@mysten/sui/jsonRpc';
import { Ed25519Keypair } from '@mysten/sui/keypairs/ed25519';
import { Transaction } from '@mysten/sui/transactions';

const [, , moduleName, functionName, ...args] = process.argv;

const requiredEnv = ['SUI_RPC_URL', 'SUI_PACKAGE_ID', 'SUI_SERVER_SECRET_KEY'];
for (const key of requiredEnv) {
    if (!process.env[key]) {
        throw new Error(`${key} is required`);
    }
}

const keypair = Ed25519Keypair.fromSecretKey(process.env.SUI_SERVER_SECRET_KEY);
const signerAddress = keypair.getPublicKey().toSuiAddress();

if (
    process.env.SUI_SERVER_ADDRESS
    && process.env.SUI_SERVER_ADDRESS.toLowerCase() !== signerAddress.toLowerCase()
) {
    throw new Error(`SUI_SERVER_ADDRESS does not match SUI_SERVER_SECRET_KEY signer (${signerAddress})`);
}

const client = new SuiJsonRpcClient({
    network: 'testnet',
    url: process.env.SUI_RPC_URL,
});

const tx = new Transaction();
if (process.env.SUI_GAS_BUDGET) {
    tx.setGasBudget(BigInt(process.env.SUI_GAS_BUDGET));
}

const target = `${process.env.SUI_PACKAGE_ID}::${moduleName}::${functionName}`;
const moveArgs = [];

if (moduleName === 'finance_profile' && functionName === 'add_savings') {
    if (args.length !== 2) {
        throw new Error('finance_profile::add_savings requires profile object ID and amount in MIST');
    }

    moveArgs.push(tx.object(args[0]));
    moveArgs.push(tx.pure.u64(args[1]));
} else if (moduleName === 'finance_profile' && functionName === 'create_profile') {
    if (args.length !== 0) {
        throw new Error('finance_profile::create_profile does not accept arguments');
    }
} else if (moduleName === 'loyalty_badge' && functionName === 'claim_milestone_badge') {
    if (args.length !== 6) {
        throw new Error('loyalty_badge::claim_milestone_badge requires recipient, tier slug, tier level, threshold cents, tier bonus bps, and stacked flag');
    }

    const [recipient, tierSlug, tierLevel, thresholdCents, tierBonusBps, isStacked] = args;

    moveArgs.push(tx.pure.address(recipient));
    moveArgs.push(tx.pure.vector('u8', new TextEncoder().encode(tierSlug)));
    moveArgs.push(tx.pure.u64(tierLevel));
    moveArgs.push(tx.pure.u64(thresholdCents));
    moveArgs.push(tx.pure.u64(tierBonusBps));
    moveArgs.push(tx.pure.bool(isStacked === '1' || isStacked === 'true'));
} else {
    throw new Error(`Unsupported Sui Move call: ${target}`);
}

tx.moveCall({
    target,
    arguments: moveArgs,
});

const result = await client.signAndExecuteTransaction({
    signer: keypair,
    transaction: tx,
    options: {
        showEffects: true,
        showEvents: true,
        showObjectChanges: true,
        showBalanceChanges: true,
    },
});

console.log(JSON.stringify(result));
