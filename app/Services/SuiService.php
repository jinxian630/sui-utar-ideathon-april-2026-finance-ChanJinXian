<?php

namespace App\Services;

use App\Models\SavingsEntry;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class SuiService
{
    private string $rpcUrl;
    private string $serverSecretKey;
    private string $packageId;
    private string $cliPath;
    private string $cliEnv;
    private string $nodePath;
    private string $syncDriver;
    private int $gasBudget;
    private ?string $serverAddress;

    public function __construct()
    {
        $this->rpcUrl = config('sui.rpc_url', 'https://public-rpc.sui-testnet.mystenlabs.com');
        $this->serverSecretKey = config('sui.server_secret_key', '');
        $this->packageId = config('sui.package_id', '0x80b954a6b2eef980de92c1f1555b8f1ed0b32d9f557714b58836257436979a17');
        $this->cliPath = config('sui.cli_path', 'sui');
        $this->cliEnv = config('sui.cli_env', 'testnet-direct');
        $this->nodePath = config('sui.node_path', 'node');
        $this->syncDriver = config('sui.sync_driver', 'sdk');
        $this->gasBudget = (int) config('sui.gas_budget', 10000000);
        $this->serverAddress = config('sui.server_address');
    }

    public function syncSavingsEntry(SavingsEntry $entry, User $user): array
    {
        $profileObjectId = $user->sui_finance_profile_id;
        $profileDigest = null;

        if (!$profileObjectId) {
            $profile = $this->createFinanceProfile();
            $profileObjectId = $profile['profile_object_id'];
            $profileDigest = $profile['digest'];
        }

        $amount = (float) $entry->amount + (float) $entry->round_up_amount;
        $digest = $this->addSavings($profileObjectId, $amount);

        return [
            'profile_object_id' => $profileObjectId,
            'profile_digest' => $profileDigest,
            'digest' => $digest,
        ];
    }

    public function createFinanceProfile(): array
    {
        $result = $this->runMoveCall('finance_profile', 'create_profile');
        $profileObjectId = $this->extractCreatedObjectId($result, 'finance_profile::FinanceProfile');

        if (!$profileObjectId) {
            throw new RuntimeException('Sui profile creation succeeded, but no FinanceProfile object ID was returned.');
        }

        return [
            'digest' => $this->extractDigest($result),
            'profile_object_id' => strtolower($profileObjectId),
        ];
    }

    public function addSavings(string $profileObjectId, float $amount): string
    {
        $amountMist = (int) round($amount * 1_000_000_000);
        if ($amountMist <= 0) {
            throw new RuntimeException('Only positive amounts can be synced to Sui.');
        }

        $result = $this->runMoveCall('finance_profile', 'add_savings', [
            $profileObjectId,
            (string) $amountMist,
        ]);

        $digest = $this->extractDigest($result);
        if (!$digest) {
            throw new RuntimeException('Sui transaction succeeded, but no digest was returned.');
        }

        return $digest;
    }

    private function runMoveCall(string $module, string $function, array $args = []): array
    {
        if ($this->syncDriver !== 'cli') {
            try {
                return $this->runSdkMoveCall($module, $function, $args);
            } catch (RuntimeException $exception) {
                if ($this->syncDriver === 'sdk') {
                    throw $exception;
                }

                Log::warning('Sui SDK call failed; falling back to CLI', [
                    'module' => $module,
                    'function' => $function,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $this->runCliMoveCall($module, $function, $args);
    }

    private function runSdkMoveCall(string $module, string $function, array $args = []): array
    {
        if (!$this->serverSecretKey) {
            throw new RuntimeException('SUI_SERVER_SECRET_KEY is required for SDK Sui publishing.');
        }

        $command = [
            $this->nodePath,
            base_path('scripts/sui-server-sync.mjs'),
            $module,
            $function,
            ...$args,
        ];

        $process = null;
        $attempts = 5;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $process = Process::timeout(90)
                ->env($this->processEnvironment())
                ->path(base_path())
                ->run($command);

            if ($process->successful() || !$this->isRetryableCliFailure($process->output(), $process->errorOutput())) {
                break;
            }

            sleep($attempt);
        }

        if ($process->failed()) {
            $message = trim($process->errorOutput())
                ?: trim($process->output())
                ?: 'Sui SDK call failed.';

            Log::error('Sui SDK call failed', [
                'command' => [$this->nodePath, 'scripts/sui-server-sync.mjs', $module, $function],
                'output' => $process->output(),
                'error' => $process->errorOutput(),
            ]);

            throw new RuntimeException($message);
        }

        return $this->decodeMoveCallResult($process->output(), 'Sui SDK');
    }

    private function runCliMoveCall(string $module, string $function, array $args = []): array
    {
        $command = [
            $this->cliPath,
            'client',
            '--client.env',
            $this->cliEnv,
            'call',
            '--package',
            $this->packageId,
            '--module',
            $module,
            '--function',
            $function,
            '--gas-budget',
            (string) $this->gasBudget,
            '--json',
        ];

        if ($this->serverAddress) {
            $command[] = '--sender';
            $command[] = $this->serverAddress;
        }

        if ($args !== []) {
            $command[] = '--args';
            array_push($command, ...$args);
        }

        $process = null;
        $attempts = 3;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $process = Process::timeout(60)
                ->env($this->processEnvironment())
                ->run($command);

            if ($process->successful() || !$this->isRetryableCliFailure($process->output(), $process->errorOutput())) {
                break;
            }

            sleep(2);
        }

        if ($process->failed()) {
            $message = trim($process->errorOutput())
                ?: trim($process->output())
                ?: 'Sui CLI call failed.';

            Log::error('Sui CLI call failed', [
                'command' => $command,
                'output' => $process->output(),
                'error' => $process->errorOutput(),
            ]);

            throw new RuntimeException($message);
        }

        return $this->decodeMoveCallResult($process->output(), 'Sui CLI');
    }

    private function decodeMoveCallResult(string $output, string $source): array
    {
        $decoded = json_decode($output, true);
        if (!is_array($decoded)) {
            throw new RuntimeException($source . ' returned invalid JSON: ' . $output);
        }

        $status = data_get($decoded, 'effects.status.status');
        if ($status && $status !== 'success') {
            throw new RuntimeException(data_get($decoded, 'effects.status.error', 'Sui transaction failed.'));
        }

        return $decoded;
    }

    private function processEnvironment(): array
    {
        return array_filter([
            'SUI_RPC_URL' => $this->rpcUrl,
            'SUI_PACKAGE_ID' => $this->packageId,
            'SUI_SERVER_ADDRESS' => $this->serverAddress,
            'SUI_SERVER_SECRET_KEY' => $this->serverSecretKey,
            'SUI_GAS_BUDGET' => (string) $this->gasBudget,
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR' => getenv('WINDIR') ?: 'C:\\Windows',
            'PATH' => getenv('PATH') ?: null,
            'USERPROFILE' => getenv('USERPROFILE') ?: null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function isRetryableCliFailure(string $output, string $errorOutput): bool
    {
        $message = strtolower($output . "\n" . $errorOutput);

        return str_contains($message, 'dns error')
            || str_contains($message, 'service is currently unavailable')
            || str_contains($message, 'transport error');
    }

    private function extractDigest(array $result): ?string
    {
        return data_get($result, 'digest')
            ?? data_get($result, 'effects.transactionDigest');
    }

    private function extractCreatedObjectId(array $result, string $objectTypeSuffix): ?string
    {
        foreach (($result['objectChanges'] ?? []) as $change) {
            if (($change['type'] ?? null) !== 'created') {
                continue;
            }

            if (str_ends_with($change['objectType'] ?? '', $objectTypeSuffix)) {
                return $change['objectId'] ?? null;
            }
        }

        foreach (($result['effects']['created'] ?? []) as $created) {
            $objectId = $created['reference']['objectId'] ?? null;

            if ($objectId) {
                return $objectId;
            }
        }

        return null;
    }

    public function submitRoundUp(string $profileObjectId, float $roundUpAmount): ?string
    {
        $amountInMist = (int) round($roundUpAmount * 1_000_000_000);
        if ($amountInMist <= 0) return null;

        try {
            $txBytes   = $this->buildAddSavingsPTB($profileObjectId, $amountInMist);
            $signature = $this->signTransactionBytes($txBytes);

            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method'  => 'sui_executeTransactionBlock',
                'params'  => [
                    $txBytes,
                    [$signature],
                    ['showEffects' => true, 'showEvents' => true],
                    'WaitForLocalExecution',
                ],
            ]);

            return $response->json('result.digest');
        } catch (\Exception $e) {
            Log::error('SuiService submitRoundUp error: ' . $e->getMessage());
            return null; // Handle missing sodium extension or connection error gracefully
        }
    }

    public function addSavingsAndStake(string $profileObjectId, string $vaultObjectId, float $amount): ?array
    {
        try {
            $amountMist = (int) round($amount * 1_000_000_000);

            $txBytes = $this->buildDualCallPTB([
                [
                    'package'  => config('sui.package_id'),
                    'function' => 'add_savings',
                    'args'     => [$profileObjectId, (string)$amountMist],
                ],
                [
                    'package'  => config('sui.package_id'),
                    'function' => 'deposit',
                    'args'     => [$vaultObjectId, (string)$amountMist],
                ],
            ]);

            $signature = $this->signTransactionBytes($txBytes);

            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'id'      => 1,
                'method'  => 'sui_executeTransactionBlock',
                'params'  => [
                    $txBytes,
                    [$signature],
                    ['showEffects' => true, 'showEvents' => true],
                    'WaitForLocalExecution',
                ],
            ]);

            $digest = $response->json('result.digest');
            return $digest ? ['digest' => $digest] : null;

        } catch (\Throwable $e) {
            Log::error('SuiService::addSavingsAndStake failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function buildDualCallPTB(array $calls): string
    {
        return base64_encode("dummy_ptb_bytes_dual_call_" . uniqid());
    }

    public function mintLoyaltyBadge(string $recipientAddress, int $streakDays): ?string
    {
        try {
            $txBytes   = $this->buildMintBadgePTB($recipientAddress, $streakDays);
            $signature = $this->signTransactionBytes($txBytes);

            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method'  => 'sui_executeTransactionBlock',
                'params'  => [
                    $txBytes,
                    [$signature],
                    ['showEffects' => true, 'showEvents' => true],
                    'WaitForLocalExecution',
                ],
            ]);

            return $response->json('result.digest');
        } catch (\Exception $e) {
            Log::error('SuiService mintLoyaltyBadge error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Call claim_milestone_badge on-chain.
     * This mints the badge NFT and emits BadgeEarned/RebateIssued events.
     * Returns badge transaction metadata on success, null on failure.
     */
    public function claimMilestoneBadge(
        string $recipientAddress,
        string $tierSlug,
        int    $tierLevel,
        int    $thresholdCents,
        int    $tierBonusBps,
        bool   $isStacked
    ): ?array {
        if (empty($recipientAddress)) {
            Log::warning('SuiService::claimMilestoneBadge skipped — no recipient address');
            return null;
        }

        try {
            $result = $this->runMoveCall('loyalty_badge', 'claim_milestone_badge', [
                $recipientAddress,
                $tierSlug,
                (string) $tierLevel,
                (string) $thresholdCents,
                (string) $tierBonusBps,
                $isStacked ? 'true' : 'false',
            ]);

            $summary = $this->summarizeBadgeMintResult($result, $tierLevel);

            Log::info("SuiService::claimMilestoneBadge success", [
                'recipient' => $recipientAddress,
                'tier'      => $tierSlug,
                'stacked'   => $isStacked,
                'digest'    => $summary['digest'],
                'object_id' => $summary['badge_object_id'],
            ]);

            return $summary;
        } catch (\Exception $e) {
            Log::error('SuiService::claimMilestoneBadge error: ' . $e->getMessage());
            return null; // Non-blocking: DB record still saved even if chain fails
        }
    }

    public function summarizeBadgeMintResult(array $result, ?int $fallbackLevel = null): array
    {
        $digest = $this->extractDigest($result);
        $event = $this->extractBadgeEarnedEvent($result);
        $objectId = $event['badge_object_id'] ?? $this->extractCreatedObjectId($result, 'loyalty_badge::LoyaltyBadge');

        return [
            'digest' => $digest,
            'badge_object_id' => $objectId ? strtolower($objectId) : null,
            'badge_level' => (int) ($event['badge_level'] ?? $fallbackLevel ?? 0),
            'suivision_url' => $objectId ? 'https://testnet.suivision.xyz/object/' . strtolower($objectId) : null,
        ];
    }

    private function extractBadgeEarnedEvent(array $result): ?array
    {
        foreach (($result['events'] ?? []) as $event) {
            $type = $event['type'] ?? '';
            if (!str_ends_with($type, '::loyalty_badge::BadgeEarned')) {
                continue;
            }

            $payload = $event['parsedJson'] ?? null;
            return is_array($payload) ? $payload : null;
        }

        return null;
    }

    private function signTransactionBytes(string $txBytesBase64): string
    {
        if (empty($this->serverSecretKey) || !extension_loaded('sodium')) {
            throw new \RuntimeException('Sodium extension not loaded or SUI_SERVER_SECRET_KEY not set.');
        }

        $txBytes   = base64_decode($txBytesBase64);
        $intent    = "\x00\x00\x00" . $txBytes; // intent scope 0
        $hash      = hash('blake2b', $intent, true, ['length' => 32]);
        $secretKey = base64_decode($this->serverSecretKey);
        $signature = sodium_crypto_sign_detached($hash, $secretKey);
        $publicKey = sodium_crypto_sign_publickey_from_secretkey($secretKey);
        
        return base64_encode("\x00" . $signature . $publicKey);
    }

    private function buildAddSavingsPTB(string $profileObjectId, int $amountInMist): string
    {
        // Placeholder for pure PHP BCS Serialization of the PTB.
        // In a real implementation, you would construct the transaction bytes using a Sui PHP SDK.
        return base64_encode("dummy_ptb_bytes_add_savings_" . $amountInMist);
    }

    private function buildMintBadgePTB(string $recipientAddress, int $streakDays): string
    {
        // Placeholder for pure PHP BCS Serialization of the PTB.
        return base64_encode("dummy_ptb_bytes_mint_badge_" . $streakDays);
    }

    /**
     * Build the PTB for claim_milestone_badge entry function.
     * In production: replace with actual BCS-serialised transaction bytes
     * using a Sui PHP SDK (e.g. calling a Node.js helper or direct BCS encoding).
     *
     * Function signature in Move:
     *   claim_milestone_badge(recipient, tier_slug, tier_level,
     *                         threshold_cents, tier_bonus_bps, is_stacked, ctx)
     */
    private function buildMilestoneBadgePTB(
        string $recipientAddress,
        string $tierSlug,
        int    $tierLevel,
        int    $thresholdCents,
        int    $tierBonusBps,
        bool   $isStacked
    ): string {
        // Placeholder: encode all args so the PTB is uniquely identifiable in logs.
        // Replace with real BCS-serialized PTB bytes when deploying to Testnet.
        $payload = json_encode([
            'function'  => 'claim_milestone_badge',
            'package'   => config('sui.package_id'),
            'module'    => 'loyalty_badge',
            'recipient' => $recipientAddress,
            'slug'      => $tierSlug,
            'level'     => $tierLevel,
            'threshold' => $thresholdCents,
            'bonus_bps' => $tierBonusBps,
            'stacked'   => $isStacked,
        ]);
        return base64_encode("ptb_milestone_badge_" . $payload);
    }
}
