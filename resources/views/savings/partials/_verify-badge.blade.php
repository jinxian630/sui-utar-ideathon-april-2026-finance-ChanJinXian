{{-- resources/views/savings/partials/_verify-badge.blade.php --}}
@if($entry->staked && $entry->stake_digest)
    <a href="https://testnet.suivision.xyz/txblock/{{ $entry->stake_digest }}"
       target="_blank" rel="noopener noreferrer" title="View on SuiVision Testnet"
       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold
              bg-purple-500/10 text-purple-300 border-purple-500/25
              hover:bg-purple-500/20 hover:border-purple-500/40 transition
              shadow-[0_0_8px_rgba(168,85,247,0.15)]">
        <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse shadow-[0_0_4px_#a855f7]"></span>
        Vault Staked
        <span class="opacity-40 font-mono text-[10px]">{{ substr($entry->stake_digest, 0, 5) }}…</span>
    </a>

@elseif($entry->synced_on_chain && $entry->sui_digest)
    <a href="https://testnet.suivision.xyz/txblock/{{ $entry->sui_digest }}"
       target="_blank" rel="noopener noreferrer" title="View on SuiVision Testnet"
       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold
              bg-emerald-500/10 text-emerald-300 border-emerald-500/25
              hover:bg-emerald-500/20 hover:border-emerald-500/40 transition
              shadow-[0_0_8px_rgba(16,185,129,0.15)]">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_4px_#10b981]"></span>
        Settled
        <span class="opacity-40 font-mono text-[10px]">{{ substr($entry->sui_digest, 0, 5) }}…</span>
    </a>

@else
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold
                 bg-amber-500/10 text-amber-300 border-amber-500/25">
        <span class="w-1.5 h-1.5 rounded-full bg-sky-300 shadow-[0_0_4px_#7dd3fc]"></span>
        Ready to Sync
    </span>
@endif
