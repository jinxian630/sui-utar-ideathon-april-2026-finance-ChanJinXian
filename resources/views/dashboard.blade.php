<x-app-layout>

    {{-- SUCCESS TOAST --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-cloak x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed top-4 right-6 z-[999] flex items-center gap-3 bg-green-500/10 border border-green-500/25 text-green-400 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium">
            ✅ {{ session('success') }}
        </div>
    @endif

    @php
        // ── Summaries from controller ──────────────────────────────────────
        $totalSaved    = $totalSaved ?? 0;
        $totalIncome   = $totalIncome   ?? 0;
        $totalExpenses = $totalExpenses ?? 0;
        $totalBalance  = $totalBalance  ?? 0;
        $lastEntry     = isset($transactions) && $transactions->isNotEmpty() ? $transactions->first() : null;

        // ── Badge milestones ───────────────────────────────────────────────
        if (!isset($milestones)) {
        $milestones = [
            1 => ['slug' => 'saver',          'threshold' => 100,   'name' => 'Saver Lv.1',    'icon' => '🥉', 'color' => 'from-amber-700 to-amber-500'],
            2 => ['slug' => 'investor',       'threshold' => 500,   'name' => 'Investor',       'icon' => '🥈', 'color' => 'from-slate-500 to-slate-300'],
            3 => ['slug' => 'wealth_builder', 'threshold' => 1000,  'name' => 'Wealth Builder', 'icon' => '🥇', 'color' => 'from-yellow-600 to-yellow-400'],
            4 => ['slug' => 'diamond_saver',  'threshold' => 5000,  'name' => 'Diamond Saver',  'icon' => '💎', 'color' => 'from-cyan-600 to-cyan-400'],
            5 => ['slug' => 'finance_master', 'threshold' => 10000, 'name' => 'Finance Master', 'icon' => '👑', 'color' => 'from-purple-600 to-violet-400'],
        ];
        }
        $earnedSlugs = $earnedSlugs ?? [];

        // ── Next unclaimed milestone & progress ────────────────────────────
        $nextMilestone = null;
        $nextLevel     = null;
        foreach ($milestones as $lvl => $m) {
            if ($totalSaved < $m['threshold']) {
                $nextMilestone = $m;
                $nextLevel     = $lvl;
                break;
            }
        }
        if (!$nextMilestone) {
            $nextMilestone = end($milestones);
            $nextLevel     = array_key_last($milestones);
        }
        $prevThreshold = $nextLevel > 1 ? $milestones[$nextLevel - 1]['threshold'] : 0;
        $nextThreshold = $nextMilestone['threshold'];
        $nextName      = $nextMilestone['name'];
        $badgeProgress = $badgeProgress ?? ($nextThreshold > 0
            ? min(100, round((($totalSaved - $prevThreshold) / ($nextThreshold - $prevThreshold)) * 100))
            : 100);
        $gap           = max(0, $nextThreshold - $totalSaved);

        // ── Dynamic chart data (last 6 months from transactions) ───────────
        $chartLabels = [];
        $chartValues = [];
        if (isset($transactions) && $transactions->isNotEmpty()) {
            $grouped = $transactions
                ->where('type', 'income')
                ->groupBy(fn($t) => $t->created_at ? $t->created_at->format('M Y') : 'Unknown');
            foreach ($grouped as $month => $items) {
                $chartLabels[] = $month;
                $chartValues[] = round($items->sum('amount'), 2);
            }
        }
        if (empty($chartLabels)) {
            $chartLabels = ['No data'];
            $chartValues = [0];
        }

        // % change vs last month
        $now = \Carbon\Carbon::now();
        $thisMonthTotal = isset($transactions)
            ? $transactions->where('type','income')
                ->filter(fn($t) => $t->created_at && $t->created_at->month === $now->month && $t->created_at->year === $now->year)
                ->sum('amount')
            : 0;
        $lastMonthTotal = isset($transactions)
            ? $transactions->where('type','income')
                ->filter(fn($t) => $t->created_at && $t->created_at->month === $now->subMonth()->month && $t->created_at->year === $now->subMonth()->year)
                ->sum('amount')
            : 0;
        $pctChange = $lastMonthTotal > 0
            ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
            : 0;
        $pctSign   = $pctChange >= 0 ? '+' : '';
    @endphp

    <div class="p-6 min-h-full">

        {{-- ===== FIX 1 + 2: UNIFIED DASHBOARD HEADER ROW ===== --}}
        <div class="flex items-start justify-between mb-6">

            {{-- LEFT: Title + Hero Balance --}}
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Dashboard</h1>
                <p class="text-xs text-gray-500 mt-0.5">This is your overview dashboard for this month</p>

                {{-- FIX 2: text-5xl Hero Balance + visibility toggle --}}
                <div x-data="{ hidden: false }" class="mt-4">
                    <div class="flex items-center gap-3">
                        <span class="text-5xl font-extrabold text-white tracking-tight" x-show="!hidden">
                            RM {{ number_format($totalBalance, 2) }}
                        </span>
                        <span class="text-5xl font-extrabold text-gray-700 tracking-tight" x-show="hidden" x-cloak>RM ••••••</span>

                        <button @click="hidden = !hidden"
                                class="text-gray-600 hover:text-purple-400 transition"
                                aria-label="Toggle balance visibility">
                            <svg x-show="!hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            <svg x-show="hidden" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/>
                                <path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Last entry note --}}
                    @if($lastEntry)
                        <p class="text-xs text-gray-500 mt-1">
                            Last transaction:
                            <span class="{{ $lastEntry->type === 'income' ? 'text-green-400' : 'text-red-400' }} font-medium">
                                {{ $lastEntry->type === 'income' ? '+' : '-' }}RM {{ number_format($lastEntry->amount, 2) }}
                            </span>
                            from <em class="not-italic text-gray-300">{{ $lastEntry->description }}</em>
                            · {{ $lastEntry->created_at ? $lastEntry->created_at->diffForHumans() : 'Unknown date' }}
                        </p>
                    @else
                        <p class="text-xs text-gray-600 mt-1">No transactions yet — add your first entry below.</p>
                    @endif
                </div>

                {{-- Action buttons (REMOVED) --}}
            </div>

            {{-- RIGHT: Date range + Export (REMOVED) --}}
        </div>

        {{-- ===== 3-COLUMN GRID ===== --}}
        <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.25rem;">

            {{-- ============ LEFT COLUMN ============ --}}
            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                {{-- COMPACT ADD TRANSACTION CARD (FIX 11: dark inputs) --}}
                <div style="background:#12121e; border:1px solid rgba(255,255,255,0.07); border-radius:1rem; padding:1.25rem; position:relative; box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <div style="position:absolute; inset:0; overflow:hidden; border-radius:1rem; pointer-events:none;">
                        <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(124,92,255,0.07);border-radius:50%;"></div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;position:relative;">
                        <h3 style="color:white;font-weight:600;font-size:0.875rem;">Add New Transaction</h3>
                        <span style="font-size:1.2rem;color:#7C5CFF;font-weight:700;">+</span>
                    </div>
                    <form method="POST" action="{{ route('transactions.store') }}" style="display:flex;flex-direction:column;gap:0.75rem;position:relative;z-index:50;">
                        @csrf
                        <div>
                            <label style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Description</label>
                            
                            <div x-data="{ 
                                open: false, 
                                desc: '{{ old('description') }}',
                                options: ['Salary', 'Food & Beverage', 'Transport', 'Utilities', 'Entertainment', 'Shopping', 'Healthcare']
                            }" class="relative w-full">
                                <input id="desc-input" type="text" name="description" required placeholder="e.g. Salary, Coffee..." 
                                       x-model="desc"
                                       @focus="open = true" 
                                       @click.outside="open = false"
                                       class="w-full bg-white border border-gray-300 rounded-xl text-gray-900 font-medium text-left text-sm pl-4 pr-10 py-2.5 placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                                       autocomplete="off">
                                
                                {{-- Arrow Icon --}}
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-gray-600" @click="open = !open">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </div>

                                {{-- Dropdown aligned to the right --}}
                                <div x-show="open" 
                                     x-transition
                                     style="display: none;"
                                     class="absolute right-0 top-full mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-[100] overflow-hidden py-1">
                                    <template x-for="opt in options" :key="opt">
                                        <div @click="desc = opt; open = false" 
                                             x-data="{ hovered: false }"
                                             @mouseenter="hovered = true"
                                             @mouseleave="hovered = false"
                                             :style="desc === opt ? 'background-color: rgba(124,92,255,0.15); color: #7C5CFF; font-weight: 700;' : (hovered ? 'background-color: rgba(124,92,255,0.08); color: #7C5CFF;' : 'color: #374151;')"
                                             class="px-4 py-2 text-sm cursor-pointer transition-colors"
                                             x-text="opt">
                                        </div>
                                    </template>
                                </div>
                            </div>
                            @error('description')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Amount (RM)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                                   class="w-full bg-white border border-gray-300 rounded-xl text-gray-900 font-medium text-sm px-4 py-2.5 placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition">
                            @error('amount')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Type</label>
                            <div style="display:flex;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;padding:0.25rem;">
                                <label style="flex:1;cursor:pointer;">
                                    <input type="radio" name="type" value="income" style="display:none;" checked
                                           onchange="document.getElementById('expense-label').style.background='transparent';document.getElementById('expense-label').style.color='#6b6b8a';this.parentElement.querySelector('div').style.background='rgba(74,222,128,0.15)';this.parentElement.querySelector('div').style.color='#4ade80';">
                                    <div id="income-label" style="text-align:center;padding:0.5rem;border-radius:0.5rem;font-size:0.8rem;font-weight:500;background:rgba(74,222,128,0.12);color:#4ade80;transition:all 0.15s;">Income</div>
                                </label>
                                <label style="flex:1;cursor:pointer;">
                                    <input type="radio" name="type" value="expense" style="display:none;"
                                           onchange="document.getElementById('income-label').style.background='transparent';document.getElementById('income-label').style.color='#6b6b8a';this.parentElement.querySelector('div').style.background='rgba(248,113,113,0.15)';this.parentElement.querySelector('div').style.color='#f87171';">
                                    <div id="expense-label" style="text-align:center;padding:0.5rem;border-radius:0.5rem;font-size:0.8rem;font-weight:500;color:#6b6b8a;transition:all 0.15s;">Expense</div>
                                </label>
                            </div>
                        </div>
                        <button type="submit"
                                style="background:linear-gradient(135deg,#7C5CFF,#4f46e5);color:white;font-weight:600;border-radius:0.75rem;padding:0.625rem;font-size:0.875rem;border:none;cursor:pointer;transition:opacity 0.2s;box-shadow:0 4px 16px rgba(124,92,255,0.35);"
                                onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Add Transaction
                        </button>
                    </form>
                </div>

                {{-- FIX 6: NUANCE WALLET CARD — dynamic name + Sui icon --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                        <div>
                            <p style="color:white;font-weight:600;font-size:0.875rem;">Nuance Wallet</p>
                            <p style="color:#5a5a7a;font-size:0.65rem;margin-top:0.15rem;">Sui Testnet · 1 USD = 4.71 MYR</p>
                        </div>
                        <button style="color:#7C5CFF;font-size:0.7rem;font-weight:600;background:rgba(124,92,255,0.1);border:1px solid rgba(124,92,255,0.2);border-radius:0.5rem;padding:0.25rem 0.625rem;cursor:pointer;">Manage</button>
                    </div>

                    {{-- Purple gradient card --}}
                    <div style="background:linear-gradient(135deg,#2a1060 0%,#3d1a8f 40%,#1e0d5e 100%);border-radius:0.875rem;padding:1.25rem;position:relative;overflow:hidden;margin-top:0.75rem;min-height:130px;">
                        <div style="position:absolute;top:0;right:0;width:60%;height:100%;background:radial-gradient(circle at 80% 50%,rgba(124,92,255,0.25),transparent 70%);pointer-events:none;"></div>
                        <div style="position:absolute;bottom:-20px;right:-20px;width:100px;height:100px;background:rgba(124,92,255,0.2);border-radius:50%;pointer-events:none;"></div>

                        {{-- FIX 6: Sui SVG icon instead of ))) --}}
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                            <div style="display:flex;align-items:center;gap:0.4rem;">
                                <svg style="width:22px;height:22px;" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="16" cy="16" r="16" fill="white" fill-opacity="0.15"/>
                                    <path d="M16 7C16 7 10 13.5 10 17.5C10 20.5 12.7 23 16 23C19.3 23 22 20.5 22 17.5C22 13.5 16 7 16 7Z" fill="white"/>
                                </svg>
                                <span style="color:rgba(255,255,255,0.7);font-size:0.6rem;letter-spacing:0.15em;font-weight:700;text-transform:uppercase;">Nuance</span>
                            </div>
                            <span style="color:rgba(255,255,255,0.4);font-size:0.6rem;letter-spacing:0.08em;">Sui Testnet</span>
                        </div>

                        <p style="color:white;font-family:monospace;font-size:0.9rem;letter-spacing:0.18em;font-weight:500;margin-bottom:1rem;">
                            @if(auth()->user()->wallet_address)
                                {{ substr(auth()->user()->wallet_address, 0, 6) }} ●●●● ●●●● {{ substr(auth()->user()->wallet_address, -4) }}
                            @else
                                0x1a2b ●●●● ●●●● 3c4d
                            @endif
                        </p>

                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            {{-- FIX 6: dynamic auth user name --}}
                            <span style="color:rgba(255,255,255,0.7);font-size:0.75rem;font-weight:500;text-transform:uppercase;letter-spacing:0.05em;">{{ auth()->user()->name }}</span>
                            <span style="color:rgba(255,255,255,0.35);font-size:0.7rem;">06/2026</span>
                        </div>
                    </div>

                    {{-- Balance under card --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.625rem;margin-top:0.75rem;">
                        <div style="background:#0a0a14;border:1px solid rgba(255,255,255,0.05);border-radius:0.75rem;padding:0.75rem;text-align:center;">
                            <p style="color:#4ade80;font-weight:700;font-size:0.875rem;">RM {{ number_format($totalIncome ?? 0, 2) }}</p>
                            <p style="color:#4a4a6a;font-size:0.6rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.08em;">Income</p>
                        </div>
                        <div style="background:#0a0a14;border:1px solid rgba(255,255,255,0.05);border-radius:0.75rem;padding:0.75rem;text-align:center;">
                            <p style="color:#f87171;font-weight:700;font-size:0.875rem;">RM {{ number_format($totalExpenses ?? 0, 2) }}</p>
                            <p style="color:#4a4a6a;font-size:0.6rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.08em;">Expenses</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============ CENTRE COLUMN ============ --}}
            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                {{-- GOAL MILESTONE TRACKER --}}
                <div x-data="{ 
                    amount: 50, 
                    isSmartDeposit: true,
                    selectedGoalId: '',
                    goals: {{ $goals->map(fn($g) => ['id' => $g->id, 'current' => (float)$g->current_amount, 'target' => (float)$g->target_amount])->toJson() }},
                    globalSaved: {{ (float) $totalSaved }},
                    globalTarget: {{ (float) $goal }},
                    get currentProgress() {
                        if (this.selectedGoalId) {
                            let g = this.goals.find(x => x.id == this.selectedGoalId);
                            if (g && g.target > 0) {
                                return Math.min(100, Math.max(0, (g.current / g.target) * 100));
                            }
                        }
                        return this.globalTarget > 0 ? Math.min(100, Math.max(0, (this.globalSaved / this.globalTarget) * 100)) : 0;
                    },
                    get currentDashoffset() {
                        return 263.89 - (this.currentProgress / 100) * 263.89;
                    }
                }" style="background:linear-gradient(180deg, #12121e 0%, #1a152e 100%);border:1px solid rgba(124,92,255,0.15);border-radius:20px;padding:1.5rem;box-shadow:0 8px 32px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);position:relative;overflow:hidden;">
                    
                    {{-- Glow background --}}
                    <div style="position:absolute;top:20%;left:50%;transform:translate(-50%,-50%);width:200px;height:200px;background:radial-gradient(circle, rgba(64,196,255,0.15) 0%, transparent 70%);pointer-events:none;filter:blur(20px);"></div>
                    <div style="position:absolute;top:0;right:0;width:100px;height:100px;background:radial-gradient(circle, rgba(124,92,255,0.2) 0%, transparent 70%);pointer-events:none;filter:blur(20px);"></div>

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;position:relative;z-index:10;">
                        <div>
                            <h3 style="color:white;font-weight:700;font-size:1rem;letter-spacing:0.02em;">Quick Deposit</h3>
                            <p style="color:#6b6b8a;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.1em;margin-top:0.2rem;">Goal Tracker</p>
                        </div>
                        <div style="background:rgba(64,196,255,0.1);border:1px solid rgba(64,196,255,0.2);color:#40c4ff;font-size:0.7rem;font-weight:700;padding:0.25rem 0.6rem;border-radius:20px;letter-spacing:0.05em;" x-text="Math.round(currentProgress) + '%'">
                            75%
                        </div>
                    </div>

                    {{-- Glowing Circular Progress Ring --}}
                    <div style="display:flex;justify-content:center;margin-bottom:1.5rem;position:relative;z-index:10;">
                        <div style="position:relative;width:120px;height:120px;display:flex;align-items:center;justify-content:center;">
                            <svg style="transform:rotate(-90deg);width:100%;height:100%;" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="8"></circle>
                                <circle cx="50" cy="50" r="42" fill="none" stroke="url(#gradientGlow)" stroke-width="8" stroke-dasharray="263.89" :stroke-dashoffset="currentDashoffset" stroke-linecap="round" style="filter:drop-shadow(0 0 8px rgba(64,196,255,0.6)); transition: stroke-dashoffset 0.6s ease;"></circle>
                                <defs>
                                    <linearGradient id="gradientGlow" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#40c4ff" />
                                        <stop offset="100%" stop-color="#7C5CFF" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div style="position:absolute;text-align:center;">
                                <p style="color:white;font-size:1.25rem;font-weight:800;line-height:1;"><span x-text="Math.round(currentProgress)">75</span><span style="font-size:0.8rem;color:#8A8A9A;">%</span></p>
                            </div>
                        </div>
                    </div>

                    {{-- Micro Stats --}}
                    <div style="text-align:center;margin-bottom:1.5rem;position:relative;z-index:10;">
                        <p style="color:#d0d0e0;font-size:0.9rem;font-weight:600;letter-spacing:0.05em;">Select a goal below</p>
                        <p style="color:#6b6b8a;font-size:0.7rem;margin-top:0.3rem;">to assign your savings deposit.</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('savings.store') }}" style="position:relative;z-index:10;">
                        @csrf
                        <input type="hidden" name="type" value="income">

                        <div style="margin-bottom:1.25rem;">
                            <select name="goal_id" required x-model="selectedGoalId" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:0.75rem;padding:0.6rem 0.8rem;font-size:0.85rem;cursor:pointer;outline:none;">
                                <option value="" style="background:#12121e;color:#a0a0b0;">-- Select Your Goal --</option>
                                @foreach($goals as $g)
                                    <option value="{{ $g->id }}" style="background:#12121e;color:white;">{{ $g->emoji }} {{ $g->name }} (Target: RM {{ number_format($g->target_amount) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.45rem;">Deposit Amount (RM)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" required x-model.number="amount"
                                   style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:white;border-radius:0.75rem;padding:0.7rem 0.85rem;font-size:0.95rem;font-weight:700;outline:none;"
                                   placeholder="Type amount to save">
                        </div>

                        {{-- Quick Action Chips (Glassmorphism) --}}
                        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:0.75rem;margin-bottom:1.5rem;">
                            <button type="button" @click="amount = 10" :style="amount === 10 ? 'background:rgba(124,92,255,0.25);border-color:rgba(124,92,255,0.5);color:white;' : 'background:rgba(255,255,255,0.03);border-color:rgba(255,255,255,0.08);color:#a0a0b0;'" style="border:1px solid;border-radius:1rem;padding:0.75rem 0.5rem;font-size:0.85rem;font-weight:700;cursor:pointer;backdrop-filter:blur(10px);transition:all 0.2s;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                +RM 10
                            </button>
                            <button type="button" @click="amount = 50" :style="amount === 50 ? 'background:rgba(124,92,255,0.25);border-color:rgba(124,92,255,0.5);color:white;' : 'background:rgba(255,255,255,0.03);border-color:rgba(255,255,255,0.08);color:#a0a0b0;'" style="border:1px solid;border-radius:1rem;padding:0.75rem 0.5rem;font-size:0.85rem;font-weight:700;cursor:pointer;backdrop-filter:blur(10px);transition:all 0.2s;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                +RM 50
                            </button>
                            <button type="button" @click="amount = 100" :style="amount === 100 ? 'background:rgba(124,92,255,0.25);border-color:rgba(124,92,255,0.5);color:white;' : 'background:rgba(255,255,255,0.03);border-color:rgba(255,255,255,0.08);color:#a0a0b0;'" style="border:1px solid;border-radius:1rem;padding:0.75rem 0.5rem;font-size:0.85rem;font-weight:700;cursor:pointer;backdrop-filter:blur(10px);transition:all 0.2s;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                +RM 100
                            </button>
                        </div>

                        {{-- Smart Deposit Web3 Toggle --}}
                        <div style="background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.05);border-radius:1rem;padding:1rem;display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;cursor:pointer;" @click="isSmartDeposit = !isSmartDeposit">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:28px;height:28px;background:rgba(64,196,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid rgba(64,196,255,0.3);">
                                    {{-- SUI Logo tiny --}}
                                    <svg style="width:14px;height:14px;" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="16" cy="16" r="16" fill="#40c4ff" fill-opacity="0.2"/>
                                        <path d="M16 7C16 7 10 13.5 10 17.5C10 20.5 12.7 23 16 23C19.3 23 22 20.5 22 17.5C22 13.5 16 7 16 7Z" fill="#40c4ff"/>
                                    </svg>
                                </div>
                                <div>
                                    <p style="color:white;font-size:0.8rem;font-weight:600;">Smart Deposit</p>
                                    <p style="color:#6b6b8a;font-size:0.65rem;">Auto-stake to Sui liquidity pool</p>
                                </div>
                            </div>
                            {{-- Custom Toggle --}}
                            <div style="width:40px;height:22px;border-radius:20px;position:relative;transition:background 0.3s;" :style="isSmartDeposit ? 'background:#40c4ff;' : 'background:rgba(255,255,255,0.1);'">
                                <div style="width:18px;height:18px;background:white;border-radius:50%;position:absolute;top:1px;transition:left 0.3s;box-shadow:0 2px 4px rgba(0,0,0,0.2);" :style="isSmartDeposit ? 'left:21px;' : 'left:1px;'"></div>
                            </div>
                            <input type="hidden" name="enable_stake" :value="isSmartDeposit ? 1 : 0">
                        </div>

                        <button type="submit"
                                style="width:100%;background:linear-gradient(135deg,#40c4ff,#7C5CFF);color:white;font-weight:700;border-radius:1rem;padding:0.875rem;font-size:0.9rem;border:none;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 20px rgba(64,196,255,0.4);"
                                onmouseover="this.style.boxShadow='0 6px 24px rgba(64,196,255,0.6)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='0 4px 20px rgba(64,196,255,0.4)';this.style.transform='translateY(0)'">
                            Deposit <span x-text="'RM ' + amount"></span>
                        </button>
                    </form>
                </div>

                {{-- SAVINGS LEDGER --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);flex:1;display:flex;flex-direction:column;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                        <div>
                            <h3 style="color:white;font-weight:600;font-size:0.875rem;">Savings Ledger</h3>
                            <p style="color:#5a5a7a;font-size:0.7rem;margin-top:0.2rem;">Track your savings progress</p>
                        </div>
                    </div>
                    
                    @if(!isset($savings) || $savings->isEmpty())
                        <div style="text-align:center;padding:2rem;border:1px dashed rgba(255,255,255,0.08);border-radius:0.75rem;margin-top:auto;margin-bottom:auto;">
                            <p style="font-size:2rem;opacity:0.15;margin-bottom:0.5rem;">🏦</p>
                            <p style="color:#4a4a6a;font-size:0.75rem;">No savings yet</p>
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:0.25rem;overflow-y:auto;scrollbar-width:thin;max-height: 250px;">
                            @foreach($savings->take(10) as $saving)
                                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0.625rem;border-radius:0.75rem;transition:background 0.15s;cursor:default;"
                                     onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
                                    <div style="width:34px;height:34px;border-radius:50%;background:rgba(124,92,255,0.12);border:1px solid rgba(124,92,255,0.2);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                                        💎
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <p style="color:#d0d0e0;font-size:0.8rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $saving->description ?: ucfirst($saving->category) }}
                                        </p>
                                        <p style="color:#4a4a6a;font-size:0.65rem;">
                                            {{ $saving->created_at ? $saving->created_at->format('d M Y') : 'Unknown date' }}
                                            @if($saving->synced_on_chain)
                                                <span class="text-green-500 ml-1">✓ Web3</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:0.5rem;flex-shrink:0;">
                                        <span style="font-size:0.8rem;font-weight:700;color:#7C5CFF">
                                            +RM {{ number_format($saving->amount, 2) }}
                                        </span>
                                        <a href="{{ route('savings.edit', $saving->id) }}"
                                           style="color:#6b6b8a;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);cursor:pointer;font-size:0.75rem;padding:0.2rem 0.35rem;border-radius:0.4rem;transition:color 0.15s;"
                                           onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='#6b6b8a'" aria-label="Edit description" title="Edit description">Edit</a>
                                           
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- ============ RIGHT COLUMN ============ --}}
            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                {{-- FIX 7 + 8: SAVING WALLET — progress bar + shortcut chips --}}
                {{-- FIX 7: SAVING WALLET — Visual Progress Ring --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);display:flex;flex-direction:column;align-items:center;">
                    <div style="display:flex;justify-content:space-between;width:100%;margin-bottom:1rem;">
                        <h3 style="color:white;font-weight:600;font-size:0.875rem;">Saving Wallet</h3>
                        <span style="color:#4ade80;font-size:0.75rem;font-weight:600;">RM {{ number_format($totalSaved, 2) }}</span>
                    </div>

                    <x-progress-ring
                        :progress="$badgeProgress"
                        :label="'Next badge: ' . $nextName . ' - ' . number_format($badgeProgress, 0) . '%'"
                        color="#7C5CFF" :radius="64" :stroke="10"
                    />

                    <p style="color:#5a5a7a;font-size:0.75rem;margin-top:1rem;">
                        RM {{ number_format($totalSaved, 2) }} / RM {{ number_format($nextThreshold, 2) }}
                    </p>
                    <p style="color:#4a4a6a;font-size:0.65rem;margin-top:0.35rem;">Next badge: {{ $nextName }}</p>

                    @if(auth()->user()->round_up_streak > 0)
                        <div style="display:flex;align-items:center;gap:0.5rem;background:rgba(124,92,255,0.15);padding:0.5rem 1rem;border-radius:50px;margin-top:1rem;border:1px solid rgba(124,92,255,0.3);">
                            <span style="font-size:1.1rem;">🔥</span>
                            <span style="font-size:0.75rem;font-weight:600;color:#a78bfa;">
                                {{ auth()->user()->round_up_streak }}-day Round-Up Streak!
                            </span>
                        </div>
                    @endif
                </div>

                {{-- FIX 8: Project-relevant shortcut chips --}}
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;">
                    <button onclick="document.getElementById('desc-input').focus()"
                            style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:0.75rem;padding:0.625rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                            onmouseover="this.style.borderColor='rgba(124,92,255,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                        <div style="font-size:1.1rem;">💰</div>
                        <p style="color:#4a4a6a;font-size:0.5rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">SAVINGS</p>
                    </button>
                    <button onclick="window.location.href='{{ route('badges') }}'"
                            style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:0.75rem;padding:0.625rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                            onmouseover="this.style.borderColor='rgba(124,92,255,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                        <div style="font-size:1.1rem;">🏅</div>
                        <p style="color:#4a4a6a;font-size:0.5rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">BADGES</p>
                    </button>
                    <button onclick="window.location.href='{{ route('profile.edit') }}'"
                            style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:0.75rem;padding:0.625rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                            onmouseover="this.style.borderColor='rgba(124,92,255,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                        <div style="font-size:1.1rem;">⛓️</div>
                        <p style="color:#4a4a6a;font-size:0.5rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">WALLET</p>
                    </button>
                    <button @click="$dispatch('open-chat')"
                            style="background:#0b0f1a;border:1px solid rgba(34,211,238,0.22);border-radius:0.75rem;padding:0.55rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s, box-shadow 0.2s;"
                            onmouseover="this.style.borderColor='rgba(34,211,238,0.55)';this.style.boxShadow='0 0 24px rgba(34,211,238,0.12)'"
                            onmouseout="this.style.borderColor='rgba(34,211,238,0.22)';this.style.boxShadow='none'">
                        <div class="cyber-ai-icon cyber-ai-icon-sm" style="margin:0 auto;width:2.25rem;height:2.25rem;font-size:0.65rem;">
                            <span>AI</span>
                        </div>
                        <p style="color:#67e8f9;font-size:0.55rem;margin-top:0.35rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;">AI CHAT</p>
                    </button>
                </div>

                {{-- FIX 3: BADGE TROPHY CABINET — colour tiers --}}
                <div id="badges-section" style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <h3 style="color:white;font-weight:600;font-size:0.875rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;">
                        🏆 Achievement Badges
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:0.4rem;">
                        @foreach($milestones as $lvl => $badge)
                            @php
                                $earned = in_array($badge['slug'] ?? '', $earnedSlugs);
                                $badgeGap = max(0, $badge['threshold'] - ($totalSaved ?? 0));
                                $badgeGapShort = $badgeGap >= 1000
                                    ? number_format($badgeGap/1000, 1) . 'k'
                                    : number_format($badgeGap, 0);
                            @endphp
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0.625rem;border-radius:0.75rem;border:1px solid {{ $earned ? 'rgba(255,255,255,0.1)' : 'rgba(255,255,255,0.04)' }};background:{{ $earned ? '#1a1a2e' : 'transparent' }};transition:all 0.2s;"
                                 onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='{{ $earned ? '#1a1a2e' : 'transparent' }}'">
                                {{-- FIX 3: Icon with gradient if earned, grayscale if locked --}}
                                <div style="display:flex;align-items:center;gap:0.625rem;">
                                    <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;
                                                {{ $earned
                                                    ? 'background:linear-gradient(135deg, var(--badge-from), var(--badge-to));'
                                                    : 'background:rgba(255,255,255,0.05);filter:grayscale(1);opacity:0.4;'
                                                }}"
                                         @if($earned)
                                         style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;
                                                @if($lvl == 1) background:linear-gradient(135deg,#92400e,#d97706);
                                                @elseif($lvl == 2) background:linear-gradient(135deg,#475569,#94a3b8);
                                                @elseif($lvl == 3) background:linear-gradient(135deg,#d97706,#fbbf24);
                                                @elseif($lvl == 4) background:linear-gradient(135deg,#0891b2,#22d3ee);
                                                @else background:linear-gradient(135deg,#7c3aed,#a78bfa);
                                                @endif"
                                         @else
                                         style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;background:rgba(255,255,255,0.05);filter:grayscale(1);opacity:0.4;"
                                         @endif>
                                        {{ $badge['icon'] }}
                                    </div>
                                    <div>
                                        <p style="color:{{ $earned ? '#e2e2f2' : '#5a5a7a' }};font-size:0.78rem;font-weight:500;">{{ $badge['name'] }}</p>
                                        <p style="color:#4a4a6a;font-size:0.6rem;">
                                            @if($earned) ✅ Earned @else 🔒 RM {{ number_format($badge['threshold']) }} milestone @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- FIX 12: Right side — k-shorthand + tooltip --}}
                                <div style="text-align:right;flex-shrink:0;min-width:70px;">
                                    @if($earned)
                                        <span style="font-size:0.65rem;color:#4ade80;font-weight:600;">✅ Earned</span>
                                    @else
                                        <span title="RM {{ number_format($badgeGap, 2) }} remaining to unlock"
                                              style="font-size:0.65rem;color:#4a4a6a;white-space:nowrap;cursor:help;">
                                            –RM {{ $badgeGapShort }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- RECENT TRANSACTIONS --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <h3 style="color:white;font-weight:600;font-size:0.875rem;margin-bottom:0.25rem;">Recent Transaction</h3>
                    <p style="color:#5a5a7a;font-size:0.7rem;margin-bottom:0.875rem;">Show your transaction history this month</p>

                    <div x-data="{ tab: 'all' }">
                        <div style="display:flex;gap:0.375rem;margin-bottom:0.875rem;">
                            <button @click="tab='all'" :style="tab==='all' ? 'background:rgba(255,255,255,0.1);color:white;border-color:rgba(255,255,255,0.2)' : 'color:#5a5a7a;border-color:rgba(255,255,255,0.05)'"
                                    style="font-size:0.7rem;font-weight:600;border:1px solid;border-radius:0.5rem;padding:0.3rem 0.75rem;cursor:pointer;background:transparent;transition:all 0.15s;">All</button>
                            <button @click="tab='income'" :style="tab==='income' ? 'background:rgba(74,222,128,0.15);color:#4ade80;border-color:rgba(74,222,128,0.25)' : 'color:#5a5a7a;border-color:rgba(255,255,255,0.05)'"
                                    style="font-size:0.7rem;font-weight:600;border:1px solid;border-radius:0.5rem;padding:0.3rem 0.75rem;cursor:pointer;background:transparent;transition:all 0.15s;">Income</button>
                            <button @click="tab='expenses'" :style="tab==='expenses' ? 'background:rgba(248,113,113,0.15);color:#f87171;border-color:rgba(248,113,113,0.25)' : 'color:#5a5a7a;border-color:rgba(255,255,255,0.05)'"
                                    style="font-size:0.7rem;font-weight:600;border:1px solid;border-radius:0.5rem;padding:0.3rem 0.75rem;cursor:pointer;background:transparent;transition:all 0.15s;">Expenses</button>
                        </div>

                        @if(!isset($transactions) || $transactions->isEmpty())
                            <div style="text-align:center;padding:2rem;border:1px dashed rgba(255,255,255,0.08);border-radius:0.75rem;">
                                <p style="font-size:2rem;opacity:0.15;margin-bottom:0.5rem;">💳</p>
                                <p style="color:#4a4a6a;font-size:0.75rem;">No transactions found</p>
                            </div>
                        @else
                            <div style="display:flex;flex-direction:column;gap:0.25rem;max-height:180px;overflow-y:auto;scrollbar-width:thin;">
                                @foreach($transactions->take(8) as $tx)
                                    <div x-show="tab === 'all' || (tab === 'income' && '{{ $tx->type }}' === 'income') || (tab === 'expenses' && '{{ $tx->type }}' === 'expense')"
                                         style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0.625rem;border-radius:0.75rem;transition:background 0.15s;cursor:default;"
                                         onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
                                        <div style="width:34px;height:34px;border-radius:50%;{{ $tx->type === 'income' ? 'background:rgba(74,222,128,0.12);border:1px solid rgba(74,222,128,0.2)' : 'background:rgba(248,113,113,0.12);border:1px solid rgba(248,113,113,0.2)' }};flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                                            {{ $tx->type === 'income' ? '💰' : '💸' }}
                                        </div>
                                        <div style="flex:1;min-width:0;">
                                            <p style="color:#d0d0e0;font-size:0.8rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $tx->description }}</p>
                                            <p style="color:#4a4a6a;font-size:0.65rem;">{{ ucfirst($tx->type) }} · {{ $tx->created_at ? $tx->created_at->format('d M Y') : 'Unknown date' }}</p>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:0.5rem;flex-shrink:0;">
                                            <span style="font-size:0.8rem;font-weight:700;{{ $tx->type === 'income' ? 'color:#4ade80' : 'color:#f87171' }}">
                                                {{ $tx->type === 'income' ? '+' : '-' }}RM {{ number_format($tx->amount, 2) }}
                                            </span>
                                            <span style="color:#4a4a6a;font-size:0.65rem;border:1px solid rgba(255,255,255,0.05);border-radius:999px;padding:0.15rem 0.45rem;">View only</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            {{-- END 3-COLUMN GRID --}}
        </div>
    </div>

    {{-- FIX 9: CHART.JS — dynamic labels/values from DB --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('savingsChart');
        if (!ctx) return;
        const g = ctx.getContext('2d');
        const grad = g.createLinearGradient(0, 0, 0, 200);
        grad.addColorStop(0, 'rgba(124,92,255,0.55)');
        grad.addColorStop(1, 'rgba(124,92,255,0.0)');

        // Dynamic data from PHP (Fix 9)
        const dynamicLabels = @json($chartLabels);
        const dynamicValues = @json($chartValues);

        window.sc = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dynamicLabels,
                datasets: [{
                    label: 'Income',
                    data: dynamicValues,
                    borderColor: '#7C5CFF',
                    backgroundColor: grad,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0f0f1a',
                    pointBorderColor: '#7C5CFF',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1a2e',
                        titleColor: '#8A8A9A',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        cornerRadius: 10,
                        callbacks: { label: c => 'RM ' + c.parsed.y.toLocaleString() }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: '#4a4a6a', font: { size: 10 } } },
                    y: { grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false }, border: { display: false }, ticks: { color: '#4a4a6a', font: { size: 10 }, maxTicksLimit: 5, callback: v => 'RM ' + v.toLocaleString() } }
                },
                interaction: { mode: 'index', intersect: false }
            }
        });

        // Update badge colour based on pct change
        const badge = document.getElementById('chart-badge');
        if (badge) {
            const pct = {{ $pctChange }};
            if (pct < 0) {
                badge.style.background = 'rgba(248,113,113,0.15)';
                badge.style.color = '#f87171';
                badge.style.borderColor = 'rgba(248,113,113,0.2)';
            }
        }
    });
    </script>

</x-app-layout>
