{{-- resources/views/savings/partials/goal-card.blade.php --}}
@php
    $color     = '#' . ltrim($goal->color ?? '7C5CFF', '#');
    $remaining = max(0, $goal->target_amount - $goal->current_amount);
    $progress  = min(100, $goal->progress_percent ?? 0);
    $hasStakeRequested = $goal->savingsEntries()
        ->where('staked', true)
        ->exists();
    $stakingRateBps = 0;

    foreach ([
        ['threshold' => 10000, 'rate_bps' => 500],
        ['threshold' => 5000, 'rate_bps' => 300],
        ['threshold' => 1000, 'rate_bps' => 200],
        ['threshold' => 500, 'rate_bps' => 150],
        ['threshold' => 100, 'rate_bps' => 100],
    ] as $tier) {
        if ($hasStakeRequested && (float) $goal->current_amount >= $tier['threshold']) {
            $stakingRateBps = $tier['rate_bps'];
            break;
        }
    }

    $stakingRebate = $stakingRateBps > 0
        ? round(((float) $goal->current_amount * $stakingRateBps) / 10000, 2)
        : 0;
    $withdrawTotal = round((float) $goal->current_amount + $stakingRebate, 2);

    $daysLeft = null;
    if ($goal->deadline) {
        $daysLeft = (int) now()->startOfDay()->diffInDays($goal->deadline, false);
    }
@endphp

<div x-data="{}" class="relative flex flex-col min-h-[240px] p-6 rounded-xl border-2
            overflow-hidden group transition-all duration-300"
     style="background-color: {{ $color }}15; border-color: {{ $color }}; box-shadow: 0 0 15px {{ $color }}40, inset 0 0 15px {{ $color }}20;">

    {{-- Ambient glow on hover --}}
    <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full blur-3xl opacity-0
                group-hover:opacity-25 transition-opacity duration-500 pointer-events-none"
         style="background-color: {{ $color }};"></div>

    {{-- ── Header ── --}}
    <div class="flex items-start justify-between mb-5 relative z-10">
        <div class="flex items-center gap-3">
            <div class="w-16 h-16 rounded-lg flex items-center justify-center text-4xl
                        border-2 shadow-inner"
                 style="background-color: {{ $color }}25; border-color: {{ $color }}; text-shadow: 0 0 10px {{ $color }}; box-shadow: 0 0 15px {{ $color }}50;">
                {{ $goal->emoji ?? '🎯' }}
            </div>
            <div>
                <h3 class="text-white font-bold text-base leading-snug max-w-[150px] truncate">
                    {{ $goal->name }}
                </h3>
                @if($daysLeft !== null)
                    @if($daysLeft > 0)
                        <p class="text-xs font-medium text-gray-500 mt-0.5">📅 {{ $daysLeft }}d left</p>
                    @elseif($daysLeft === 0)
                        <p class="text-xs font-bold text-amber-400 mt-0.5">🔔 Ends today!</p>
                    @else
                        <p class="text-xs font-medium text-rose-400 mt-0.5">⚠️ {{ abs($daysLeft) }}d overdue</p>
                    @endif
                @else
                    <p class="text-xs text-gray-600 mt-0.5">No deadline</p>
                @endif
            </div>
        </div>

        {{-- % badge (Cyberpunk style) --}}
        <span class="px-3 py-1 rounded-sm border-b-2 font-black text-xs tracking-wider uppercase"
              style="color: #fff; border-color: {{ $color }}; background-color: {{ $color }}; text-shadow: 0 0 5px rgba(255,255,255,0.5); box-shadow: 0 4px 10px {{ $color }}80;">
            {{ number_format($progress, 0) }}%
        </span>
    </div>

    {{-- ── Amount stats ── --}}
    <div class="flex justify-between items-end mb-3 relative z-10">
        <div>
            <p class="text-[10px] uppercase tracking-widest text-gray-600 font-semibold">Saved</p>
            <p class="text-xl font-black text-white leading-none mt-1">
                RM {{ number_format($goal->current_amount, 2) }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-[10px] uppercase tracking-widest text-gray-600 font-semibold">Target</p>
            <p class="text-sm font-semibold text-gray-400 mt-1">
                RM {{ number_format($goal->target_amount, 2) }}
            </p>
        </div>
    </div>

    {{-- ── Animated progress bar (Cyberpunk neon) ── --}}
    <div class="w-full h-3 rounded-none bg-gray-900 border border-gray-700 overflow-hidden relative z-10 mb-4">
        <div class="h-full rounded-none transition-all duration-1000 ease-out relative"
             style="width: {{ $progress }}%; background-color: {{ $color }}; box-shadow: 0 0 15px {{ $color }}, 0 0 30px {{ $color }};">
            <div class="absolute inset-0 bg-white/20"></div>
        </div>
    </div>

    {{-- ── Footer actions ── --}}
    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-4 border-t border-white/5 relative z-10">
        @if($remaining > 0)
            <p class="text-xs font-medium text-gray-500">
                <span class="text-white font-bold">RM {{ number_format($remaining, 2) }}</span> remaining
            </p>
        @else
            <div class="flex flex-wrap items-center gap-2 min-w-0">
                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                    🎉 Goal Reached!
                </span>
                {{-- Withdraw button (only when 100% complete) --}}
                <button type="button"
                        @click="$dispatch('open-modal', 'withdraw-goal-{{ $goal->id }}')"
                        class="px-3 py-1 rounded-lg flex items-center gap-1 text-xs font-bold transition-all duration-200 shrink-0"
                        style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); color: #34d399;"
                        onmouseover="this.style.background='rgba(16,185,129,0.28)'; this.style.borderColor='rgba(16,185,129,0.7)';"
                        onmouseout="this.style.background='rgba(16,185,129,0.15)'; this.style.borderColor='rgba(16,185,129,0.4)';">
                    💸 Withdraw
                </button>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            {{-- Edit button → triggers Edit Modal --}}
            <button type="button"
                    @click="$dispatch('open-modal', 'edit-goal-{{ $goal->id }}')"
                    class="px-3 py-2 rounded-lg flex items-center gap-1.5 text-sm font-semibold transition-all duration-200 shrink-0"
                    style="background: rgba(124,92,255,0.12); border: 1px solid rgba(124,92,255,0.35); color: #a78bfa;"
                    onmouseover="this.style.background='rgba(124,92,255,0.25)'; this.style.borderColor='rgba(124,92,255,0.7)';"
                    onmouseout="this.style.background='rgba(124,92,255,0.12)'; this.style.borderColor='rgba(124,92,255,0.35)';">
                ✏️ Edit
            </button>

            {{-- Delete button → triggers Delete Modal --}}
            <button type="button"
                    @click="$dispatch('open-modal', 'delete-goal-{{ $goal->id }}')"
                    class="px-3 py-2 rounded-lg flex items-center gap-1.5 text-sm font-semibold transition-all duration-200 shrink-0"
                    style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #f87171;"
                    onmouseover="this.style.background='rgba(239,68,68,0.22)'; this.style.borderColor='rgba(239,68,68,0.65)';"
                    onmouseout="this.style.background='rgba(239,68,68,0.1)'; this.style.borderColor='rgba(239,68,68,0.3)';">
                🗑️ Delete
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     EDIT GOAL MODAL (Cyberpunk glassmorphism)
════════════════════════════════════════════ --}}
<x-modal name="edit-goal-{{ $goal->id }}" focusable>
    <div style="background: linear-gradient(135deg, #0f0f1a 0%, #1a1030 100%);
                border: 1px solid rgba(124,92,255,0.4);
                border-radius: 1.25rem;
                box-shadow: 0 0 40px rgba(124,92,255,0.25), inset 0 1px 0 rgba(255,255,255,0.06);
                padding: 2rem;
                position: relative;
                overflow: hidden;">

        {{-- Glow accent top-right --}}
        <div style="position:absolute;top:-40px;right:-40px;width:160px;height:160px;
                    background:radial-gradient(circle, rgba(124,92,255,0.35) 0%, transparent 70%);
                    pointer-events:none;filter:blur(20px);"></div>

        {{-- Header --}}
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem;">
            <div style="width:36px;height:36px;border-radius:0.6rem;background:rgba(124,92,255,0.2);
                        border:1px solid rgba(124,92,255,0.4);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                ✏️
            </div>
            <div>
                <h2 style="color:white;font-size:1.1rem;font-weight:700;letter-spacing:-0.3px;">Edit Goal</h2>
                <p style="color:#6b6b8a;font-size:0.7rem;margin-top:0.1rem;">{{ $goal->emoji }} {{ $goal->name }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('goals.update', $goal->id) }}" style="position:relative;z-index:10;">
            @csrf
            @method('PUT')

            {{-- Goal Name --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.65rem;color:#7C5CFF;text-transform:uppercase;
                              letter-spacing:0.12em;font-weight:700;margin-bottom:0.5rem;">
                    ◈ Goal Name
                </label>
                <input type="text" name="name" value="{{ $goal->name }}" required maxlength="255"
                       placeholder="e.g. My Dream Car"
                       style="width:100%;background:#0a0a16;border:1px solid rgba(124,92,255,0.4);
                              border-radius:0.75rem;color:#e2e2f2;padding:0.7rem 0.875rem;
                              font-size:0.9rem;font-weight:600;outline:none;box-sizing:border-box;
                              transition:border-color 0.2s, box-shadow 0.2s;"
                       onfocus="this.style.borderColor='rgba(124,92,255,0.8)'; this.style.boxShadow='0 0 0 3px rgba(124,92,255,0.15)';"
                       onblur="this.style.borderColor='rgba(124,92,255,0.4)'; this.style.boxShadow='none';">
            </div>

            {{-- Emoji / Icon --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.65rem;color:#7C5CFF;text-transform:uppercase;
                              letter-spacing:0.12em;font-weight:700;margin-bottom:0.5rem;">
                    ◈ Goal Icon
                </label>
                <select name="emoji" required
                        style="width:100%;background:#0a0a16;border:1px solid rgba(124,92,255,0.4);
                               border-radius:0.75rem;color:#e2e2f2;padding:0.7rem 0.875rem;
                               font-size:0.9rem;font-weight:600;outline:none;box-sizing:border-box;
                               cursor:pointer;transition:border-color 0.2s, box-shadow 0.2s;"
                        onfocus="this.style.borderColor='rgba(124,92,255,0.8)'; this.style.boxShadow='0 0 0 3px rgba(124,92,255,0.15)';"
                        onblur="this.style.borderColor='rgba(124,92,255,0.4)'; this.style.boxShadow='none';">
                    <option value="🎯"  {{ $goal->emoji === '🎯'  ? 'selected' : '' }}>🎯 General Goal</option>
                    <option value="🏠"  {{ $goal->emoji === '🏠'  ? 'selected' : '' }}>🏠 House / Real Estate</option>
                    <option value="🚗"  {{ $goal->emoji === '🚗'  ? 'selected' : '' }}>🚗 Car / Vehicle</option>
                    <option value="✈️" {{ $goal->emoji === '✈️' ? 'selected' : '' }}>✈️ Travel / Vacation</option>
                    <option value="💻"  {{ $goal->emoji === '💻'  ? 'selected' : '' }}>💻 Gadgets / Tech</option>
                    <option value="🎓"  {{ $goal->emoji === '🎓'  ? 'selected' : '' }}>🎓 Education / Studies</option>
                    <option value="💍"  {{ $goal->emoji === '💍'  ? 'selected' : '' }}>💍 Wedding / Event</option>
                    <option value="🏥"  {{ $goal->emoji === '🏥'  ? 'selected' : '' }}>🏥 Emergency Fund</option>
                    <option value="🏖️" {{ $goal->emoji === '🏖️' ? 'selected' : '' }}>🏖️ Retirement</option>
                    <option value="💰"  {{ $goal->emoji === '💰'  ? 'selected' : '' }}>💰 Savings</option>
                    <option value="🎮"  {{ $goal->emoji === '🎮'  ? 'selected' : '' }}>🎮 Gaming</option>
                    <option value="📱"  {{ $goal->emoji === '📱'  ? 'selected' : '' }}>📱 Smartphone</option>
                </select>
            </div>

            {{-- Target Amount --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.65rem;color:#7C5CFF;text-transform:uppercase;
                              letter-spacing:0.12em;font-weight:700;margin-bottom:0.5rem;">
                    ◈ Target Amount (RM)
                </label>
                <div style="position:relative;">
                    <span style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);
                                 color:#7C5CFF;font-weight:700;font-size:0.9rem;">RM</span>
                    <input type="number" name="target_amount" step="0.01" min="0.01"
                           value="{{ $goal->target_amount }}" required
                           style="width:100%;background:#0a0a16;border:1px solid rgba(124,92,255,0.4);
                                  border-radius:0.75rem;color:#e2e2f2;padding:0.7rem 0.875rem 0.7rem 2.5rem;
                                  font-size:0.9rem;font-weight:600;outline:none;box-sizing:border-box;
                                  transition:border-color 0.2s, box-shadow 0.2s;"
                           onfocus="this.style.borderColor='rgba(124,92,255,0.8)'; this.style.boxShadow='0 0 0 3px rgba(124,92,255,0.15)';"
                           onblur="this.style.borderColor='rgba(124,92,255,0.4)'; this.style.boxShadow='none';">
                </div>
            </div>

            {{-- Color Picker --}}
            <div style="margin-bottom:1.75rem;">
                <label style="display:block;font-size:0.65rem;color:#7C5CFF;text-transform:uppercase;
                              letter-spacing:0.12em;font-weight:700;margin-bottom:0.5rem;">
                    ◈ Card Color Theme
                </label>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <input type="color" name="color" value="{{ $color }}"
                           style="width:48px;height:48px;border-radius:0.6rem;border:2px solid rgba(124,92,255,0.5);
                                  background:transparent;cursor:pointer;padding:2px;"
                           id="color-picker-{{ $goal->id }}">
                    <div style="flex:1;display:flex;gap:0.4rem;flex-wrap:wrap;">
                        @foreach(['#7C5CFF','#40c4ff','#f59e0b','#10b981','#f43f5e','#8b5cf6','#06b6d4','#ec4899'] as $preset)
                            <button type="button"
                                    onclick="document.getElementById('color-picker-{{ $goal->id }}').value='{{ $preset }}'"
                                    style="width:28px;height:28px;border-radius:50%;background:{{ $preset }};
                                           border:2px solid rgba(255,255,255,0.15);cursor:pointer;transition:transform 0.15s;
                                           box-shadow:0 0 8px {{ $preset }}80;"
                                    onmouseover="this.style.transform='scale(1.2)'"
                                    onmouseout="this.style.transform='scale(1)'">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" x-on:click="$dispatch('close')"
                        style="padding:0.6rem 1.25rem;border-radius:0.75rem;font-size:0.875rem;font-weight:600;
                               background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);
                               color:#9ca3af;cursor:pointer;transition:all 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:0.6rem 1.5rem;border-radius:0.75rem;font-size:0.875rem;font-weight:700;
                               background:linear-gradient(135deg,#7C5CFF,#4f46e5);border:none;color:white;
                               cursor:pointer;box-shadow:0 4px 16px rgba(124,92,255,0.45);transition:opacity 0.2s;
                               letter-spacing:0.03em;"
                        onmouseover="this.style.opacity='0.85'"
                        onmouseout="this.style.opacity='1'">
                    ⚡ Save Changes
                </button>
            </div>
        </form>
    </div>
</x-modal>

{{-- ═══════════════════════════════════════════════════
     DELETE GOAL MODAL (Cyberpunk danger confirmation)
══════════════════════════════════════════════════════ --}}
<x-modal name="delete-goal-{{ $goal->id }}" focusable>
    <div style="background: linear-gradient(135deg, #100a0a 0%, #1a0a0f 100%);
                border: 1px solid rgba(239,68,68,0.45);
                border-radius: 1.25rem;
                box-shadow: 0 0 40px rgba(239,68,68,0.2), inset 0 1px 0 rgba(255,255,255,0.04);
                padding: 2rem;
                position: relative;
                overflow: hidden;">

        {{-- Red glow accent --}}
        <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;
                    background:radial-gradient(circle, rgba(239,68,68,0.3) 0%, transparent 70%);
                    pointer-events:none;filter:blur(24px);"></div>
        <div style="position:absolute;bottom:-40px;left:-20px;width:140px;height:140px;
                    background:radial-gradient(circle, rgba(220,38,38,0.2) 0%, transparent 70%);
                    pointer-events:none;filter:blur(20px);"></div>

        {{-- Warning Icon --}}
        <div style="display:flex;justify-content:center;margin-bottom:1.25rem;">
            <div style="width:64px;height:64px;border-radius:50%;
                        background:rgba(239,68,68,0.12);border:2px solid rgba(239,68,68,0.5);
                        display:flex;align-items:center;justify-content:center;font-size:1.75rem;
                        box-shadow:0 0 20px rgba(239,68,68,0.35), inset 0 0 10px rgba(239,68,68,0.1);">
                ⚠️
            </div>
        </div>

        {{-- Text --}}
        <div style="text-align:center;margin-bottom:1.75rem;position:relative;z-index:10;">
            <h2 style="color:white;font-size:1.15rem;font-weight:800;letter-spacing:0.02em;margin-bottom:0.5rem;">
                DELETE GOAL
            </h2>
            <div style="font-family:monospace;font-size:0.75rem;color:#f87171;letter-spacing:0.1em;
                        margin-bottom:1rem;text-transform:uppercase;">
                ▸ {{ $goal->emoji }} {{ $goal->name }} ◂
            </div>
            <p style="color:#9ca3af;font-size:0.85rem;line-height:1.6;">
                This action will <span style="color:#f87171;font-weight:700;">permanently archive</span>
                this goal and all its tracking data.
            </p>
            <p style="color:#6b7280;font-size:0.75rem;margin-top:0.5rem;">
                This cannot be undone.
            </p>
        </div>

        {{-- Neon divider --}}
        <div style="height:1px;background:linear-gradient(to right, transparent, rgba(239,68,68,0.5), transparent);margin-bottom:1.5rem;"></div>

        {{-- Action Buttons --}}
        <div style="display:flex;gap:0.75rem;position:relative;z-index:10;">
            <button type="button" x-on:click="$dispatch('close')"
                    style="flex:1;padding:0.7rem;border-radius:0.75rem;font-size:0.875rem;font-weight:600;
                           background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);
                           color:#9ca3af;cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                Cancel
            </button>

            <form method="POST" action="{{ route('goals.destroy', $goal->id) }}" style="flex:1;">
                @csrf
                @method('DELETE')
                <button type="submit"
                        style="width:100%;padding:0.7rem;border-radius:0.75rem;font-size:0.875rem;font-weight:700;
                               background:linear-gradient(135deg,#dc2626,#991b1b);border:1px solid rgba(239,68,68,0.5);
                               color:white;cursor:pointer;letter-spacing:0.05em;text-transform:uppercase;
                               box-shadow:0 4px 20px rgba(239,68,68,0.4);transition:all 0.2s;"
                        onmouseover="this.style.boxShadow='0 4px 28px rgba(239,68,68,0.65)';this.style.opacity='0.9'"
                        onmouseout="this.style.boxShadow='0 4px 20px rgba(239,68,68,0.4)';this.style.opacity='1'">
                    🗑️ Confirm Delete
                </button>
            </form>
        </div>
    </div>
</x-modal>

{{-- ══════════════════════════════════════════════════════════════
     WITHDRAW GOAL MODAL (Cyberpunk teal/emerald success design)
══════════════════════════════════════════════════════════════ --}}
@if($remaining <= 0)
<x-modal name="withdraw-goal-{{ $goal->id }}" focusable>
    <div style="background: linear-gradient(135deg, #071510 0%, #0a1f1a 100%);
                border: 1px solid rgba(16,185,129,0.45);
                border-radius: 1.25rem;
                box-shadow: 0 0 40px rgba(16,185,129,0.2), inset 0 1px 0 rgba(255,255,255,0.04);
                padding: 2rem;
                position: relative;
                overflow: hidden;">

        {{-- Teal glow accents --}}
        <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;
                    background:radial-gradient(circle, rgba(16,185,129,0.3) 0%, transparent 70%);
                    pointer-events:none;filter:blur(24px);"></div>
        <div style="position:absolute;bottom:-40px;left:-20px;width:140px;height:140px;
                    background:radial-gradient(circle, rgba(52,211,153,0.15) 0%, transparent 70%);
                    pointer-events:none;filter:blur(20px);"></div>

        {{-- Success Icon --}}
        <div style="display:flex;justify-content:center;margin-bottom:1.25rem;">
            <div style="width:64px;height:64px;border-radius:50%;
                        background:rgba(16,185,129,0.12);border:2px solid rgba(16,185,129,0.5);
                        display:flex;align-items:center;justify-content:center;font-size:1.75rem;
                        box-shadow:0 0 20px rgba(16,185,129,0.35), inset 0 0 10px rgba(16,185,129,0.1);">
                💸
            </div>
        </div>

        {{-- Text --}}
        <div style="text-align:center;margin-bottom:1.75rem;position:relative;z-index:10;">
            <h2 style="color:white;font-size:1.15rem;font-weight:800;letter-spacing:0.02em;margin-bottom:0.5rem;">
                WITHDRAW GOAL
            </h2>
            <div style="font-family:monospace;font-size:0.75rem;color:#34d399;letter-spacing:0.1em;
                        margin-bottom:1rem;text-transform:uppercase;">
                ▸ {{ $goal->emoji }} {{ $goal->name }} ◂
            </div>
            <p style="color:#9ca3af;font-size:0.85rem;line-height:1.6;">
                You are about to withdraw
                <span style="color:#34d399;font-weight:700;">RM {{ number_format($withdrawTotal, 2) }}</span>
                back to your wallet.
            </p>
            @if($stakingRebate > 0)
                <div style="margin-top:0.9rem;padding:0.8rem;border-radius:0.85rem;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);text-align:left;">
                    <p style="color:#9ca3af;font-size:0.75rem;margin-bottom:0.35rem;">
                        Principal: <span style="color:#e5e7eb;font-weight:700;">RM {{ number_format($goal->current_amount, 2) }}</span>
                    </p>
                    <p style="color:#9ca3af;font-size:0.75rem;margin-bottom:0.35rem;">
                        Staking rebate: <span style="color:#34d399;font-weight:700;">RM {{ number_format($stakingRebate, 2) }}</span>
                        <span style="color:#6b7280;">({{ number_format($stakingRateBps / 100, 1) }}%)</span>
                    </p>
                    <p style="color:#9ca3af;font-size:0.75rem;">
                        Total payout: <span style="color:#34d399;font-weight:800;">RM {{ number_format($withdrawTotal, 2) }}</span>
                    </p>
                </div>
            @endif
            <p style="color:#6b7280;font-size:0.75rem;margin-top:0.5rem;">
                This goal will be archived after withdrawal.
            </p>
            <p style="color:#6b7280;font-size:0.75rem;margin-top:0.5rem;line-height:1.5;">
                Staking service records remain visible in your savings history after withdrawal.
            </p>
        </div>

        {{-- Neon divider --}}
        <div style="height:1px;background:linear-gradient(to right, transparent, rgba(16,185,129,0.5), transparent);margin-bottom:1.5rem;"></div>

        {{-- Action Buttons --}}
        <div style="display:flex;gap:0.75rem;position:relative;z-index:10;">
            <button type="button" x-on:click="$dispatch('close')"
                    style="flex:1;padding:0.7rem;border-radius:0.75rem;font-size:0.875rem;font-weight:600;
                           background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);
                           color:#9ca3af;cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                Cancel
            </button>

            <form method="POST" action="{{ route('goals.withdraw', $goal->id) }}" style="flex:1;">
                @csrf
                <button type="submit"
                        style="width:100%;padding:0.7rem;border-radius:0.75rem;font-size:0.875rem;font-weight:700;
                               background:linear-gradient(135deg,#059669,#065f46);border:1px solid rgba(16,185,129,0.5);
                               color:white;cursor:pointer;letter-spacing:0.05em;text-transform:uppercase;
                               box-shadow:0 4px 20px rgba(16,185,129,0.4);transition:all 0.2s;"
                        onmouseover="this.style.boxShadow='0 4px 28px rgba(16,185,129,0.65)';this.style.opacity='0.9'"
                        onmouseout="this.style.boxShadow='0 4px 20px rgba(16,185,129,0.4)';this.style.opacity='1'">
                    💸 Confirm Withdraw
                </button>
            </form>
        </div>
    </div>
</x-modal>
@endif
