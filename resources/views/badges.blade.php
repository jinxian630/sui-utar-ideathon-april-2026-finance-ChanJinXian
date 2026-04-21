<x-app-layout>

    @php
        $earnedCount = 0;
        foreach ($milestones as $lvl => $badge) {
            if (($totalSaved ?? 0) >= $badge['threshold']) $earnedCount++;
        }
        $totalBadges = count($milestones);
    @endphp

    <div class="p-6 min-h-full">

        {{-- PAGE HEADER --}}
        <div class="flex items-start justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Achievement Badges</h1>
                <p class="text-xs text-gray-500 mt-0.5">Track your savings milestones and unlock rewards</p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-2 text-xs text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 border border-white/8 rounded-xl px-4 py-2 transition-all">
                ← Back to Dashboard
            </a>
        </div>

        {{-- SUMMARY STATS ROW --}}
        <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">

            {{-- Total Saved --}}
            <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(124,92,255,0.08);border-radius:50%;pointer-events:none;"></div>
                <p style="color:#5a5a7a;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.5rem;">Total Savings</p>
                <p style="color:white;font-size:1.75rem;font-weight:800;letter-spacing:-0.5px;">RM {{ number_format($totalSaved, 2) }}</p>
                <p style="color:#4a4a6a;font-size:0.65rem;margin-top:0.25rem;">Cumulative income saved</p>
            </div>

            {{-- Badges Earned --}}
            <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(74,222,128,0.06);border-radius:50%;pointer-events:none;"></div>
                <p style="color:#5a5a7a;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.5rem;">Badges Earned</p>
                <p style="color:#4ade80;font-size:1.75rem;font-weight:800;letter-spacing:-0.5px;">{{ $earnedCount }} / {{ $totalBadges }}</p>
                <p style="color:#4a4a6a;font-size:0.65rem;margin-top:0.25rem;">Keep saving to unlock more!</p>
            </div>

            {{-- Next Milestone --}}
            <div style="background:#12121e;border:1px solid rgba(124,92,255,0.15);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(124,92,255,0.12);position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(124,92,255,0.12);border-radius:50%;pointer-events:none;"></div>
                <p style="color:#7C5CFF;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.5rem;">Next Milestone</p>
                <p style="color:white;font-size:1.3rem;font-weight:700;">{{ $nextMilestone['icon'] }} {{ $nextName }}</p>
                <p style="color:#4a4a6a;font-size:0.65rem;margin-top:0.25rem;">RM {{ number_format($nextMilestone['threshold'], 0) }} target</p>
            </div>
        </div>

        {{-- PROGRESS BAR --}}
        <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                <div>
                    <p style="color:white;font-size:0.875rem;font-weight:600;">Progress to {{ $nextName }}</p>
                    <p style="color:#5a5a7a;font-size:0.7rem;margin-top:0.15rem;">
                        RM {{ number_format($totalSaved, 2) }} of RM {{ number_format($nextMilestone['threshold'], 2) }}
                    </p>
                </div>
                <span style="background:linear-gradient(135deg,rgba(124,92,255,0.2),rgba(79,70,229,0.2));color:#a78bfa;border:1px solid rgba(124,92,255,0.3);border-radius:2rem;font-size:0.8rem;font-weight:700;padding:0.3rem 0.9rem;">
                    {{ $pct }}%
                </span>
            </div>

            {{-- Multi-segment milestone bar --}}
            <div style="position:relative;height:10px;background:#0a0a14;border-radius:50px;overflow:visible;margin-bottom:0.75rem;">
                <div style="height:100%;border-radius:50px;background:linear-gradient(90deg,#7C5CFF,#a78bfa);width:{{ $pct }}%;transition:width 1.2s ease;position:relative;z-index:1;"></div>
            </div>

            {{-- Milestone markers --}}
            <div style="display:flex;justify-content:space-between;">
                @foreach($milestones as $lvl => $m)
                    @php $done = ($totalSaved ?? 0) >= $m['threshold']; @endphp
                    <div style="text-align:center;flex:1;">
                        <div style="width:20px;height:20px;border-radius:50%;margin:0 auto 0.25rem;display:flex;align-items:center;justify-content:center;font-size:0.65rem;
                            {{ $done ? 'background:linear-gradient(135deg,#7C5CFF,#a78bfa);color:white;box-shadow:0 0 8px rgba(124,92,255,0.4);' : 'background:#1a1a2e;border:1px solid rgba(255,255,255,0.08);color:#4a4a6a;' }}">
                            {{ $done ? '✓' : $lvl }}
                        </div>
                        <p style="font-size:0.55rem;color:{{ $done ? '#a78bfa' : '#3a3a5a' }};white-space:nowrap;">{{ $m['threshold'] >= 1000 ? number_format($m['threshold']/1000,0).'k' : $m['threshold'] }}</p>
                    </div>
                @endforeach
            </div>

            @if($gap > 0)
                <p style="color:#4a4a6a;font-size:0.7rem;margin-top:0.75rem;text-align:center;">
                    💡 Save <span style="color:#a78bfa;font-weight:600;">RM {{ number_format($gap, 2) }}</span> more to unlock <strong style="color:white;">{{ $nextName }}</strong>
                </p>
            @else
                <p style="color:#4ade80;font-size:0.7rem;margin-top:0.75rem;text-align:center;font-weight:600;">🎉 All milestones reached! You're a Finance Master!</p>
            @endif
        </div>

        {{-- BADGE CARDS GRID --}}
        <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            @foreach($milestones as $lvl => $badge)
                @php
                    $earned   = ($totalSaved ?? 0) >= $badge['threshold'];
                    $badgeGap = max(0, $badge['threshold'] - ($totalSaved ?? 0));

                    // Progress within this badge tier
                    $prevT    = $lvl > 1 ? $milestones[$lvl - 1]['threshold'] : 0;
                    $tierPct  = $badge['threshold'] > 0
                        ? min(100, round((max(0, ($totalSaved ?? 0) - $prevT) / ($badge['threshold'] - $prevT)) * 100))
                        : 100;

                    // Gradient colour per tier
                    $gradients = [
                        1 => 'linear-gradient(135deg,#92400e,#d97706)',
                        2 => 'linear-gradient(135deg,#475569,#94a3b8)',
                        3 => 'linear-gradient(135deg,#d97706,#fbbf24)',
                        4 => 'linear-gradient(135deg,#0891b2,#22d3ee)',
                        5 => 'linear-gradient(135deg,#7c3aed,#a78bfa)',
                    ];
                    $grad = $gradients[$lvl];
                @endphp

                <div style="background:#12121e;border:1px solid {{ $earned ? 'rgba(124,92,255,0.2)' : 'rgba(255,255,255,0.06)' }};border-radius:1rem;padding:1.5rem;box-shadow:{{ $earned ? '0 4px 24px rgba(124,92,255,0.12)' : '0 4px 16px rgba(0,0,0,0.3)' }};position:relative;overflow:hidden;transition:all 0.2s;"
                     onmouseover="this.style.borderColor='rgba(124,92,255,0.35)';this.style.transform='translateY(-2px)';"
                     onmouseout="this.style.borderColor='{{ $earned ? 'rgba(124,92,255,0.2)' : 'rgba(255,255,255,0.06)' }}';this.style.transform='translateY(0)';">

                    {{-- Background glow for earned --}}
                    @if($earned)
                        <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;background:radial-gradient(circle,rgba(124,92,255,0.12),transparent 70%);pointer-events:none;"></div>
                    @endif

                    {{-- Top row: icon + status badge --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1rem;">
                        <div style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0;
                            {{ $earned ? 'background:'.$grad.';box-shadow:0 4px 16px rgba(0,0,0,0.4);' : 'background:rgba(255,255,255,0.04);filter:grayscale(1);opacity:0.35;' }}">
                            {{ $badge['icon'] }}
                        </div>
                        @if($earned)
                            <span style="background:rgba(74,222,128,0.12);color:#4ade80;border:1px solid rgba(74,222,128,0.2);border-radius:2rem;font-size:0.65rem;font-weight:700;padding:0.25rem 0.65rem;white-space:nowrap;">
                                ✅ Earned
                            </span>
                        @else
                            <span style="background:rgba(255,255,255,0.04);color:#4a4a6a;border:1px solid rgba(255,255,255,0.06);border-radius:2rem;font-size:0.65rem;font-weight:600;padding:0.25rem 0.65rem;white-space:nowrap;">
                                🔒 Locked
                            </span>
                        @endif
                    </div>

                    {{-- Badge name + threshold --}}
                    <h3 style="color:{{ $earned ? 'white' : '#5a5a7a' }};font-size:1rem;font-weight:700;margin-bottom:0.25rem;">
                        {{ $badge['name'] }}
                    </h3>
                    <p style="color:#4a4a6a;font-size:0.7rem;margin-bottom:1rem;">
                        Reach <span style="color:#7C5CFF;font-weight:600;">RM {{ number_format($badge['threshold']) }}</span> in total savings
                    </p>

                    {{-- Per-badge progress bar --}}
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:0.3rem;">
                            <span style="font-size:0.6rem;color:#4a4a6a;">Progress</span>
                            <span style="font-size:0.6rem;color:{{ $earned ? '#4ade80' : '#7C5CFF' }};font-weight:600;">{{ $tierPct }}%</span>
                        </div>
                        <div style="background:#0a0a14;border-radius:50px;height:5px;overflow:hidden;">
                            <div style="height:100%;border-radius:50px;background:{{ $earned ? 'linear-gradient(90deg,#4ade80,#22c55e)' : 'linear-gradient(90deg,#7C5CFF,#a78bfa)' }};width:{{ $tierPct }}%;transition:width 1s ease;"></div>
                        </div>
                        @if(!$earned)
                            <p style="color:#3a3a5a;font-size:0.6rem;margin-top:0.3rem;">
                                RM {{ number_format($badgeGap, 2) }} remaining
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- MOTIVATIONAL FOOTER --}}
        <div style="margin-top:1.5rem;background:linear-gradient(135deg,rgba(124,92,255,0.08),rgba(79,70,229,0.08));border:1px solid rgba(124,92,255,0.15);border-radius:1rem;padding:1.5rem;text-align:center;">
            <p style="color:#a78bfa;font-size:1.1rem;font-weight:700;margin-bottom:0.4rem;">🚀 Keep saving — every RM counts!</p>
            <p style="color:#5a5a7a;font-size:0.75rem;">Your badges reflect your financial discipline. Unlock them all to become a Finance Master.</p>
        </div>

    </div>

</x-app-layout>
