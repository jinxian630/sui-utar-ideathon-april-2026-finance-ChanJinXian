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
        $totalSaved    = $totalIncome   ?? 0;
        $totalExpenses = $totalExpenses ?? 0;
        $totalBalance  = $totalBalance  ?? 0;
        $lastEntry     = isset($transactions) && $transactions->isNotEmpty() ? $transactions->first() : null;

        // ── Badge milestones ───────────────────────────────────────────────
        $milestones = [
            1 => ['threshold' => 100,   'name' => 'Saver Lv.1',    'icon' => '🥉', 'color' => 'from-amber-700 to-amber-500'],
            2 => ['threshold' => 500,   'name' => 'Investor',       'icon' => '🥈', 'color' => 'from-slate-500 to-slate-300'],
            3 => ['threshold' => 1000,  'name' => 'Wealth Builder', 'icon' => '🥇', 'color' => 'from-yellow-600 to-yellow-400'],
            4 => ['threshold' => 5000,  'name' => 'Diamond Saver',  'icon' => '💎', 'color' => 'from-cyan-600 to-cyan-400'],
            5 => ['threshold' => 10000, 'name' => 'Finance Master', 'icon' => '👑', 'color' => 'from-purple-600 to-violet-400'],
        ];

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
        $pct           = $nextThreshold > 0
            ? min(100, round((($totalSaved - $prevThreshold) / ($nextThreshold - $prevThreshold)) * 100))
            : 100;
        $gap           = max(0, $nextThreshold - $totalSaved);

        // ── Dynamic chart data (last 6 months from transactions) ───────────
        $chartLabels = [];
        $chartValues = [];
        if (isset($transactions) && $transactions->isNotEmpty()) {
            $grouped = $transactions
                ->where('type', 'income')
                ->groupBy(fn($t) => $t->created_at->format('M Y'));
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
                ->filter(fn($t) => $t->created_at->month === $now->month && $t->created_at->year === $now->year)
                ->sum('amount')
            : 0;
        $lastMonthTotal = isset($transactions)
            ? $transactions->where('type','income')
                ->filter(fn($t) => $t->created_at->month === $now->subMonth()->month && $t->created_at->year === $now->subMonth()->year)
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
                            · {{ $lastEntry->created_at->diffForHumans() }}
                        </p>
                    @else
                        <p class="text-xs text-gray-600 mt-1">No transactions yet — add your first entry below.</p>
                    @endif
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-3 mt-4">
                    <button onclick="document.getElementById('desc-input').focus()"
                            class="flex items-center gap-2 px-4 py-2 bg-[#12121e] border border-white/8 rounded-xl text-xs text-gray-400 hover:bg-white/5 transition">
                        💸 Move Money
                    </button>
                    <button onclick="document.getElementById('desc-input').focus()"
                            class="flex items-center gap-2 px-4 py-2 bg-[#12121e] border border-white/8 rounded-xl text-xs text-gray-400 hover:bg-white/5 transition">
                        📥 Request
                    </button>
                    <button onclick="document.getElementById('desc-input').focus()"
                            class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl text-xs text-white font-semibold shadow-[0_4px_14px_rgba(124,92,255,0.4)] hover:opacity-90 transition">
                        Transfer →
                    </button>
                </div>
            </div>

            {{-- RIGHT: Date range + Export --}}
            <div class="flex items-center gap-2 mt-1 shrink-0">
                <div class="flex items-center gap-2 bg-[#12121e] border border-white/8 rounded-xl px-4 py-2 text-xs text-gray-400 cursor-pointer hover:border-purple-500/30 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-purple-400 shrink-0">
                        <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd"/>
                    </svg>
                    05/01/2026 – 30/04/2026
                </div>
                <button class="bg-[#12121e] border border-white/8 rounded-xl p-2.5 text-gray-500 hover:text-gray-200 hover:bg-white/5 transition"
                        aria-label="Export">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z"/>
                        <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ===== 3-COLUMN GRID ===== --}}
        <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.25rem;">

            {{-- ============ LEFT COLUMN ============ --}}
            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                {{-- COMPACT ADD TRANSACTION CARD (FIX 11: dark inputs) --}}
                <div style="background:#12121e; border:1px solid rgba(255,255,255,0.07); border-radius:1rem; padding:1.25rem; position:relative; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(124,92,255,0.07);border-radius:50%;pointer-events:none;"></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                        <h3 style="color:white;font-weight:600;font-size:0.875rem;">Add New Transaction</h3>
                        <span style="font-size:1.2rem;color:#7C5CFF;font-weight:700;">+</span>
                    </div>
                    <form method="POST" action="{{ route('transactions.store') }}" style="display:flex;flex-direction:column;gap:0.75rem;">
                        @csrf
                        <div>
                            <label style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Description</label>
                            {{-- FIX 11: dark bg input --}}
                            <input id="desc-input" type="text" name="description" required placeholder="e.g. Salary, Coffee..."
                                   class="w-full bg-[#0a0a14] border border-white/8 rounded-xl text-white text-sm px-4 py-2.5 placeholder-gray-600 focus:outline-none focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/20 transition"
                                   autocomplete="off">
                            @error('description')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Amount (RM)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                                   class="w-full bg-[#0a0a14] border border-white/8 rounded-xl text-white text-sm px-4 py-2.5 placeholder-gray-600 focus:outline-none focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/20 transition">
                            @error('amount')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Type</label>
                            <div style="display:flex;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;padding:0.25rem;">
                                <label style="flex:1;cursor:pointer;">
                                    <input type="radio" name="type" value="income" style="display:none;" checked
                                           onchange="this.parentElement.querySelector('div').style.background='rgba(74,222,128,0.15)';this.parentElement.querySelector('div').style.color='#4ade80';">
                                    <div id="income-label" style="text-align:center;padding:0.5rem;border-radius:0.5rem;font-size:0.8rem;font-weight:500;background:rgba(74,222,128,0.12);color:#4ade80;transition:all 0.15s;">Income</div>
                                </label>
                                <label style="flex:1;cursor:pointer;">
                                    <input type="radio" name="type" value="expense" style="display:none;"
                                           onchange="document.getElementById('income-label').style.background='transparent';document.getElementById('income-label').style.color='#6b6b8a';this.parentElement.querySelector('div').style.background='rgba(248,113,113,0.15)';this.parentElement.querySelector('div').style.color='#f87171';">
                                    <div style="text-align:center;padding:0.5rem;border-radius:0.5rem;font-size:0.8rem;font-weight:500;color:#6b6b8a;transition:all 0.15s;">Expense</div>
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

                {{-- FIX 9: ACTIVITIES CHART — dynamic dates from DB --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);overflow:hidden;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                        <div>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <h3 style="color:white;font-weight:600;font-size:0.875rem;">Activities</h3>
                                <span id="chart-badge"
                                      style="background:rgba(74,222,128,0.15);color:#4ade80;border:1px solid rgba(74,222,128,0.2);border-radius:4px;font-size:0.6rem;font-weight:700;padding:0.1rem 0.4rem;">
                                    {{ $pctSign }}{{ $pctChange }}%
                                </span>
                            </div>
                            <p style="color:#5a5a7a;font-size:0.7rem;margin-top:0.2rem;">Show your money flow vs last month</p>
                        </div>
                        <div x-data="{ type: 'line' }" style="display:flex;gap:0.25rem;">
                            <button @click="type='line'; window.sc && (window.sc.config.type='line') && window.sc.update()"
                                    :style="type=='line' ? 'color:#7C5CFF' : 'color:#4a4a6a'"
                                    style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;padding:0.25rem 0.5rem;border-radius:0.375rem;border:none;background:transparent;cursor:pointer;transition:color 0.15s;">Line</button>
                            <button @click="type='bar'; window.sc && (window.sc.config.type='bar') && window.sc.update()"
                                    :style="type=='bar' ? 'color:#7C5CFF' : 'color:#4a4a6a'"
                                    style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;padding:0.25rem 0.5rem;border-radius:0.375rem;border:none;background:transparent;cursor:pointer;transition:color 0.15s;">Bar</button>
                        </div>
                    </div>
                    <div style="height:220px;position:relative;">
                        <canvas id="savingsChart"></canvas>
                    </div>
                </div>

                {{-- FIX 4: AI ROBO ADVISOR — purple gradient button (not white) --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,0.4);position:relative;overflow:hidden;flex:1;">
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;overflow:hidden;">
                        <div style="position:absolute;top:15px;left:15px;width:8px;height:8px;border-radius:50%;background:#7C5CFF;opacity:0.4;"></div>
                        <div style="position:absolute;top:30px;right:20px;width:5px;height:5px;border-radius:50%;background:#4f46e5;opacity:0.5;"></div>
                        <div style="position:absolute;bottom:20px;left:25px;width:10px;height:10px;border-radius:50%;background:#7C5CFF;opacity:0.2;filter:blur(2px);"></div>
                        <div style="position:absolute;bottom:15px;right:15px;width:6px;height:6px;border-radius:50%;background:#4f46e5;opacity:0.4;"></div>
                    </div>
                    <div style="width:56px;height:56px;margin:0 auto 1rem;border-radius:50%;background:linear-gradient(135deg,#7C5CFF,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 0 20px rgba(124,92,255,0.5);">🤖</div>
                    <h3 style="color:white;font-weight:700;font-size:1.05rem;line-height:1.4;margin-bottom:0.5rem;">Invest Smarter with our<br>AI-Robo Advisor!</h3>
                    <p style="color:#5a5a7a;font-size:0.75rem;line-height:1.6;margin-bottom:1rem;">Get automated management, real-time<br>insights and personalized advice</p>

                    {{-- FIX 4: Purple gradient button (NOT white) --}}
                    <button @click="$dispatch('open-chat')"
                            style="width:100%;background:linear-gradient(135deg,#7C5CFF,#4f46e5);color:white;font-weight:600;border-radius:0.75rem;padding:0.625rem;font-size:0.875rem;border:none;cursor:pointer;transition:opacity 0.2s;box-shadow:0 4px 16px rgba(124,92,255,0.35);"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        Try Now →
                    </button>
                </div>

            </div>

            {{-- ============ RIGHT COLUMN ============ --}}
            <div style="display:flex; flex-direction:column; gap:1.25rem;">

                {{-- FIX 7 + 8: SAVING WALLET — progress bar + shortcut chips --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.25rem;">
                        <div>
                            <h3 style="color:white;font-weight:600;font-size:0.875rem;">Saving Wallet</h3>
                            <p style="color:#5a5a7a;font-size:0.7rem;margin-top:0.2rem;line-height:1.5;">Allocates your income. Remember<br>be patient</p>
                        </div>
                        <button onclick="document.getElementById('desc-input').focus()"
                                style="color:#7C5CFF;font-size:0.7rem;font-weight:600;background:rgba(124,92,255,0.1);border:1px solid rgba(124,92,255,0.2);border-radius:0.5rem;padding:0.25rem 0.625rem;cursor:pointer;white-space:nowrap;">Add new</button>
                    </div>
                    <p style="color:white;font-size:1.75rem;font-weight:800;letter-spacing:-0.5px;margin-top:0.625rem;">
                        RM {{ number_format($totalIncome ?? 0, 2) }}
                    </p>
                    <p style="color:#4a4a6a;font-size:0.6rem;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.875rem;">Total Savings (Income)</p>

                    {{-- FIX 7: Visual progress bar to next badge --}}
                    <div class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span style="font-size:0.65rem;color:#7a7a9a;">
                                {{ $pct }}% toward <span style="color:#7C5CFF;font-weight:600;">{{ $nextName }}</span>
                            </span>
                            <span style="font-size:0.6rem;color:#4a4a6a;">RM {{ number_format($nextThreshold, 0) }}</span>
                        </div>
                        <div style="background:#0a0a14;border-radius:50px;height:6px;overflow:hidden;">
                            <div style="height:100%;border-radius:50px;background:linear-gradient(90deg,#7C5CFF,#a78bfa);width:{{ $pct }}%;transition:width 1s ease;"></div>
                        </div>
                        @if($gap > 0)
                            <p style="color:#4a4a6a;font-size:0.6rem;margin-top:0.3rem;">RM {{ number_format($gap, 2) }} remaining</p>
                        @else
                            <p style="color:#4ade80;font-size:0.6rem;margin-top:0.3rem;">🎉 Ready to claim your next badge!</p>
                        @endif
                    </div>

                    {{-- FIX 8: Project-relevant shortcut chips --}}
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;margin-top:0.875rem;">
                        <button onclick="document.getElementById('desc-input').focus()"
                                style="background:#0a0a14;border:1px solid rgba(255,255,255,0.05);border-radius:0.75rem;padding:0.625rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                                onmouseover="this.style.borderColor='rgba(124,92,255,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'">
                            <div style="font-size:1.1rem;">💰</div>
                            <p style="color:#4a4a6a;font-size:0.5rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">SAVINGS</p>
                        </button>
                        <button onclick="window.location.href='{{ route('badges') }}'"
                                style="background:#0a0a14;border:1px solid rgba(255,255,255,0.05);border-radius:0.75rem;padding:0.625rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                                onmouseover="this.style.borderColor='rgba(124,92,255,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'">
                            <div style="font-size:1.1rem;">🏅</div>
                            <p style="color:#4a4a6a;font-size:0.5rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">BADGES</p>
                        </button>
                        <button onclick="window.location.href='{{ route('profile.edit') }}'"
                                style="background:#0a0a14;border:1px solid rgba(255,255,255,0.05);border-radius:0.75rem;padding:0.625rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                                onmouseover="this.style.borderColor='rgba(124,92,255,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'">
                            <div style="font-size:1.1rem;">⛓️</div>
                            <p style="color:#4a4a6a;font-size:0.5rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">WALLET</p>
                        </button>
                        <button @click="$dispatch('open-chat')"
                                style="background:#0a0a14;border:1px solid rgba(255,255,255,0.05);border-radius:0.75rem;padding:0.625rem 0.25rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
                                onmouseover="this.style.borderColor='rgba(124,92,255,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'">
                            <div style="font-size:1.1rem;">🤖</div>
                            <p style="color:#4a4a6a;font-size:0.5rem;margin-top:0.2rem;text-transform:uppercase;letter-spacing:0.05em;">AI CHAT</p>
                        </button>
                    </div>
                </div>

                {{-- FIX 3: BADGE TROPHY CABINET — colour tiers --}}
                <div id="badges-section" style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <h3 style="color:white;font-weight:600;font-size:0.875rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;">
                        🏆 Achievement Badges
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:0.4rem;">
                        @foreach($milestones as $lvl => $badge)
                            @php
                                $earned = ($totalSaved ?? 0) >= $badge['threshold'];
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
                                            <p style="color:#4a4a6a;font-size:0.65rem;">{{ ucfirst($tx->type) }} · {{ $tx->created_at->format('d M Y') }}</p>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:0.5rem;flex-shrink:0;">
                                            <span style="font-size:0.8rem;font-weight:700;{{ $tx->type === 'income' ? 'color:#4ade80' : 'color:#f87171' }}">
                                                {{ $tx->type === 'income' ? '+' : '-' }}RM {{ number_format($tx->amount, 2) }}
                                            </span>
                                            <form method="POST" action="{{ route('transactions.destroy', $tx->id) }}" style="display:inline;" onsubmit="return confirm('Delete?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" style="color:#3a3a5a;background:transparent;border:none;cursor:pointer;font-size:0.75rem;padding:0.2rem;border-radius:0.3rem;transition:color 0.15s;"
                                                        onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#3a3a5a'" aria-label="Delete" title="Delete">🗑️</button>
                                            </form>
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
