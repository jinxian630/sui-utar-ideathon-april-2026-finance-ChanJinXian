{{-- resources/views/savings/partials/history-table.blade.php --}}

<div class="rounded-2xl border border-white/5 overflow-hidden shadow-xl bg-[#12121e]">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5" role="table" aria-label="Savings transaction history">
            <thead>
                <tr class="bg-black/20 text-[10px] uppercase tracking-widest text-gray-500">
                    <th scope="col" class="px-5 py-4 font-bold text-left w-8"></th>
                    <th scope="col" class="px-5 py-4 font-bold text-left">Date</th>
                    <th scope="col" class="px-5 py-4 font-bold text-left">Description</th>
                    <th scope="col" class="px-5 py-4 font-bold text-left">Goal</th>
                    <th scope="col" class="px-5 py-4 font-bold text-right">Amount</th>
                    <th scope="col" class="px-5 py-4 font-bold text-center">On-Chain</th>
                    <th scope="col" class="px-5 py-4 font-bold text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($entries as $entry)

                {{-- ── Main row (clickable to expand) ── --}}
                <tr x-data="{ open: false }"
                    class="hover:bg-white/[0.025] transition-colors group cursor-pointer"
                    @click="open = !open"
                    role="row">

                    {{-- Expand chevron --}}
                    <td class="pl-5 pr-2 py-4 w-8">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center
                                    border border-white/5 bg-white/5 text-gray-500
                                    group-hover:border-indigo-500/30 group-hover:text-indigo-400 transition"
                             :class="open ? 'rotate-90 text-indigo-400 border-indigo-500/30' : ''"
                             style="transition: transform .2s ease;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3">
                                <path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L9.19 8 6.22 5.03a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </td>

                    {{-- Date --}}
                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-sm font-semibold text-gray-200">
                            {{ ($entry->entry_date ?? $entry->created_at)->format('d M Y') }}
                        </p>
                        <p class="text-[10px] uppercase tracking-wide text-gray-600 mt-0.5">
                            {{ $entry->created_at->format('h:i A') }}
                        </p>
                    </td>

                    {{-- Description --}}
                    <td class="px-5 py-4">
                        <p class="text-sm font-medium text-gray-200 truncate max-w-[180px]">{{ $entry->note ?? '—' }}</p>
                        @if($entry->description)
                            <p class="text-[11px] text-gray-500 truncate max-w-[180px] mt-0.5">{{ $entry->description }}</p>
                        @endif
                    </td>

                    {{-- Goal --}}
                    <td class="px-5 py-4">
                        @if($entry->goal)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                                         bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                {{ $entry->goal->emoji }} {{ $entry->goal->name }}
                            </span>
                        @else
                            <span class="text-xs font-medium text-gray-600 bg-white/5 px-3 py-1 rounded-full border border-white/5">General</span>
                        @endif
                    </td>

                    {{-- Amount --}}
                    <td class="px-5 py-4 text-right">
                        <span class="text-sm font-black tracking-tight
                            {{ $entry->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $entry->type === 'income' ? '+' : '−' }}RM {{ number_format($entry->amount, 2) }}
                        </span>
                        @if($entry->round_up_amount > 0)
                            <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-wide mt-1
                                      bg-indigo-500/10 inline-block px-1.5 py-0.5 rounded">
                                +RM {{ number_format($entry->round_up_amount, 2) }} round-up
                            </p>
                        @endif
                    </td>

                    {{-- On-chain badge --}}
                    <td class="px-5 py-4 text-center" data-sui-status-cell @click.stop>
                        @include('savings.partials._verify-badge', ['entry' => $entry])
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-4 text-center" @click.stop>
                        <div class="flex items-center justify-center gap-2">
                            @unless($entry->synced_on_chain && $entry->sui_digest)
                                <form method="POST" action="{{ route('sui.savings.mark-on-chain', $entry) }}" class="inline sui-sync-form">
                                    @csrf
                                    <button type="submit"
                                            title="Sync to Sui Testnet"
                                            aria-label="Sync transaction #{{ $entry->id }} to Sui Testnet"
                                            class="sui-sync-entry w-8 h-8 rounded-full bg-sky-500/10 border border-sky-500/25 flex items-center justify-center
                                                   text-sky-300 hover:text-sky-100 hover:bg-sky-500/20 hover:border-sky-400/40 transition"
                                            data-entry-id="{{ $entry->id }}"
                                            data-amount="{{ (float) $entry->amount + (float) $entry->round_up_amount }}"
                                            data-mark-url="{{ route('sui.savings.mark-on-chain', $entry) }}">
                                        <svg class="w-4 h-4 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 16.5V3.75m0 0 4.5 4.5M12 3.75l-4.5 4.5M4.5 16.5v1.875A2.625 2.625 0 0 0 7.125 21h9.75a2.625 2.625 0 0 0 2.625-2.625V16.5" />
                                        </svg>
                                    </button>
                                </form>
                            @endunless
                            <a href="{{ route('savings.edit', $entry) }}"
                               title="Edit"
                               class="w-8 h-8 rounded-full bg-white/5 border border-white/5 flex items-center justify-center text-sm
                                      text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 hover:border-indigo-500/30 transition">
                                ✏️
                            </a>
                        </div>
                    </td>
                </tr>

                {{-- ── Expanded detail panel ── --}}
                <tr x-show="open" x-collapse x-cloak class="bg-black/30 border-b border-white/5">
                    <td colspan="7" class="px-6 py-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                            {{-- Transaction Detail Card — Sky / Cyan --}}
                            <div class="relative overflow-hidden p-4 rounded-xl border border-sky-500/20"
                                 style="background: linear-gradient(135deg, #071e2e 0%, #0f0f1a 60%);">
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-sky-400 to-cyan-400"></div>
                                <div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full blur-2xl bg-sky-500 opacity-15 pointer-events-none"></div>
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-6 h-6 rounded-lg bg-sky-500/20 border border-sky-500/30 flex items-center justify-center text-xs">📄</div>
                                    <p class="text-[10px] uppercase tracking-widest font-bold text-sky-400/70">Transaction Detail</p>
                                </div>
                                <div class="space-y-1.5 text-sm">
                                    <p><span class="text-sky-400/50 w-20 inline-block">ID</span> <span class="text-gray-300 font-mono">#{{ str_pad($entry->id, 6, '0', STR_PAD_LEFT) }}</span></p>
                                    <p><span class="text-sky-400/50 w-20 inline-block">Type</span> <span class="text-gray-300">{{ ucfirst($entry->type) }}</span></p>
                                    <p><span class="text-sky-400/50 w-20 inline-block">Note</span> <span class="text-gray-300">{{ $entry->note ?? '—' }}</span></p>
                                    <p>
                                        <span class="text-sky-400/50 w-20 inline-block">Staking</span>
                                        @if($entry->staked && $entry->stake_digest)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/25 bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-300">
                                                Staked
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/25 bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-300">
                                                Queued / not staked yet
                                            </span>
                                        @endif
                                    </p>
                                    @if($entry->round_up_amount > 0)
                                    <p><span class="text-sky-400/50 w-20 inline-block">Round-up</span> <span class="text-sky-300 font-bold">+RM {{ number_format($entry->round_up_amount, 2) }}</span></p>
                                    @endif
                                </div>
                            </div>

                            {{-- Blockchain Card — Purple / Violet --}}
                            <div class="relative overflow-hidden p-4 rounded-xl border border-violet-500/20"
                                 style="background: linear-gradient(135deg, #160e2e 0%, #0f0f1a 60%);">
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-violet-400 to-purple-400"></div>
                                <div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full blur-2xl bg-violet-600 opacity-20 pointer-events-none"></div>
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-6 h-6 rounded-lg bg-violet-500/20 border border-violet-500/30 flex items-center justify-center text-xs">⛓️</div>
                                    <p class="text-[10px] uppercase tracking-widest font-bold text-violet-400/70">Blockchain Status</p>
                                </div>
                                @php
                                    $digest = $entry->stake_digest ?? $entry->sui_digest ?? null;
                                @endphp
                                @if($digest)
                                    <div class="space-y-1.5 text-sm">
                                        <p><span class="text-violet-400/50 w-20 inline-block">Network</span> <span class="text-gray-300">Sui Testnet</span></p>
                                        <p class="flex items-center gap-2 flex-wrap">
                                            <span class="text-violet-400/50 w-20 inline-block shrink-0">Digest</span>
                                            <a href="https://testnet.suivision.xyz/txblock/{{ $digest }}"
                                               target="_blank"
                                               class="text-violet-300 hover:text-violet-200 font-mono text-xs truncate max-w-[140px] underline underline-offset-2">
                                                {{ $digest }}
                                            </a>
                                        </p>
                                        <p class="flex items-center gap-1.5 mt-3">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_6px_#34d399]"></span>
                                            <span class="text-emerald-300 font-bold text-xs">Settled — Instant Finality</span>
                                        </p>
                                    </div>
                                @else
                                    <div class="flex items-start gap-2 mt-1">
                                        <svg class="w-4 h-4 text-amber-400 animate-spin mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <div>
                                            <p class="text-amber-300 font-bold text-sm">Pending Sync</p>
                                            <p class="text-violet-300/50 text-xs mt-1 leading-relaxed">Queued in the blockchain worker. Will settle on Sui Testnet shortly.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Goal Impact Card — Amber / Gold --}}
                            @if($entry->goal)
                            <div class="relative overflow-hidden p-4 rounded-xl border border-amber-500/20
                                        flex flex-col items-center justify-center text-center"
                                 style="background: linear-gradient(135deg, #241800 0%, #0f0f1a 60%);">
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-400 to-orange-400"></div>
                                <div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full blur-2xl bg-amber-500 opacity-15 pointer-events-none"></div>
                                <p class="text-[10px] uppercase tracking-widest font-bold text-amber-400/70 mb-3">Goal Impact</p>
                                <span class="text-4xl mb-2 drop-shadow-[0_0_8px_rgba(245,158,11,0.4)]">{{ $entry->goal->emoji }}</span>
                                <p class="text-sm font-bold text-white">{{ $entry->goal->name }}</p>
                                @php
                                    $impact = $entry->goal->target_amount > 0
                                        ? (($entry->amount + ($entry->round_up_amount ?? 0)) / $entry->goal->target_amount) * 100
                                        : 0;
                                @endphp
                                <p class="text-xs text-amber-300 mt-1 font-bold">
                                    +{{ number_format($impact, 2) }}% progress from this deposit
                                </p>
                            </div>
                            @else
                            <div class="p-4 rounded-xl border border-white/5 bg-white/[0.02] flex items-center justify-center">
                                <p class="text-sm text-gray-600">No goal linked</p>
                            </div>
                            @endif

                        </div>
                    </td>
                </tr>

                @empty
                {{-- ── Empty State ── --}}
                <tr>
                    <td colspan="7" class="px-5 py-20 text-center">
                        <div class="max-w-md mx-auto relative">
                            <div class="absolute inset-0 bg-gradient-to-b from-indigo-500/5 via-transparent to-transparent rounded-3xl pointer-events-none"></div>
                            <div class="relative z-10 bg-[#12121e] border border-white/5 rounded-3xl p-10 shadow-2xl">
                                <div class="w-20 h-20 mx-auto bg-gray-900 border border-gray-800 rounded-full
                                            flex items-center justify-center text-4xl shadow-inner mb-6">
                                    🏦
                                </div>
                                <h3 class="text-xl font-bold text-white mb-2">No transactions yet</h3>
                                <p class="text-sm text-gray-400 leading-relaxed mb-8">
                                    Start logging your savings to track progress toward your goals and earn on-chain verification badges.
                                </p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="{{ route('savings.create') }}"
                                       class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold
                                              rounded-xl transition shadow-lg shadow-indigo-500/25">
                                        + Log First Save
                                    </a>
                                    <button x-data x-on:click="$dispatch('open-modal', 'create-goal')"
                                            class="px-5 py-2.5 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white
                                                   text-sm font-semibold rounded-xl border border-white/10 transition">
                                        Create a Goal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($entries->hasPages())
    <div class="mt-8 flex justify-center sm:justify-end">
        {{ $entries->links() }}
    </div>
@endif
