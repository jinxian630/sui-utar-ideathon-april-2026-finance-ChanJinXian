<x-app-layout>

    <div class="p-6 min-h-full"
         id="sui-sync-page"
         data-package-id="{{ config('sui.package_id') }}"
         data-wallet-address="{{ $user->wallet_address ?? $user->sui_address ?? '' }}"
         data-profile-object-id="{{ $user->sui_finance_profile_id ?? '' }}"
         data-profile-store-url="{{ route('sui.profile.store') }}">
        {{-- Page Header --}}
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Finance Goal Tracker</h1>
                <p class="text-xs text-gray-500 mt-0.5">Track your goal progress, staking status, and milestone rebate rewards.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
                @if(session('suivision_url'))
                    <a href="{{ session('suivision_url') }}" target="_blank" rel="noopener noreferrer"
                       class="ml-2 font-bold underline underline-offset-2 hover:text-emerald-100">
                        View on SuiVision
                    </a>
                @endif
            </div>
        @endif

        @if($errors->has('sui_sync'))
            <div class="mb-5 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                {{ $errors->first('sui_sync') }}
            </div>
        @endif

        {{-- Staking Service Education --}}
        <section class="mb-8 overflow-hidden rounded-2xl border border-emerald-500/20 bg-[#0b1713] shadow-xl shadow-emerald-950/20">
            <div class="grid gap-6 p-5 lg:grid-cols-[1.2fr_0.8fr] lg:p-6">
                <div class="relative">
                    <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.22em] text-emerald-300/80">Sui Staking Service</p>
                    <h2 class="text-xl font-black tracking-tight text-white sm:text-2xl">
                        Make every goal deposit eligible for vault staking.
                    </h2>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-300">
                        Enable staking when you log a savings deposit and the entry will be queued for the Sui liquidity vault.
                        Staked and on-chain verified deposits can support stronger milestone rebate rewards through the existing stacking bonus flow.
                    </p>
                    <div class="mt-4 rounded-xl border border-emerald-500/15 bg-emerald-500/5 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-300/80">Milestone rebate rates</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-5">
                            @foreach([
                                ['RM 100', '1.0%'],
                                ['RM 500', '1.5%'],
                                ['RM 1,000', '2.0%'],
                                ['RM 5,000', '3.0%'],
                                ['RM 10,000', '5.0%'],
                            ] as [$threshold, $rate])
                                <div class="rounded-lg border border-white/5 bg-black/20 px-3 py-2">
                                    <p class="text-[11px] font-semibold text-gray-400">{{ $threshold }}</p>
                                    <p class="text-base font-black text-emerald-300">{{ $rate }}</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs leading-5 text-emerald-100/70">
                            Unlocking multiple milestones in the same month can apply the existing 1.2x stacking bonus to later rebate rewards.
                        </p>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('savings.create') }}"
                           class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-gray-950 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:ring-offset-2 focus:ring-offset-[#0b1713]">
                            Start Staking a Save
                        </a>
                        <p class="text-xs text-emerald-200/70">
                            You can verify staked entries later from Savings History.
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                    <p class="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-gray-400">How it works</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        @foreach(['Pick a goal', 'Enable staking service', 'Save and verify on-chain', 'Withdraw after goal completion'] as $step)
                            <div class="flex items-center gap-3 rounded-lg border border-white/5 bg-white/[0.03] px-3 py-2.5">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-xs font-black text-emerald-300 ring-1 ring-emerald-500/30">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="text-sm font-semibold text-gray-200">{{ $step }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Goals Section (Scroll View) --}}
        <div class="flex overflow-x-auto gap-4 mb-8 pb-4 snap-x snap-mandatory">
            @foreach($goals as $goal)
                <div class="w-72 shrink-0 snap-start">
                    @include('savings.partials.goal-card', ['goal' => $goal])
                </div>
            @endforeach

            {{-- Add Goal CTA --}}
            <div class="w-72 shrink-0 snap-start flex flex-col items-center justify-center gap-2 p-5 rounded-xl border-2 border-dashed border-gray-600 bg-gray-800/50 text-gray-400 cursor-pointer hover:border-indigo-400 hover:text-indigo-400 hover:bg-gray-800 transition group"
                 x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-goal')">
                <span class="text-3xl group-hover:scale-110 transition-transform">+</span>
                <p class="text-xs font-medium">New Goal</p>
            </div>
        </div>

        {{-- Create Goal Modal --}}
        <x-modal name="create-goal" :show="false" focusable>
            <form method="POST" action="{{ route('goals.store') }}" class="p-6 bg-gray-800 text-white rounded-xl">
                @csrf
                <h2 class="text-lg font-bold mb-4">Create New Goal</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Goal Name</label>
                    <input type="text" name="name" required class="w-full bg-white border border-gray-700 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Target Amount (RM)</label>
                    <input type="number" name="target_amount" step="0.01" min="0.01" required class="w-full bg-white border border-gray-700 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Emoji</label>
                        <select name="emoji" class="w-full bg-white border border-gray-700 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500">
                            <option value="🎯">🎯 General Goal</option>
                            <option value="🏠">🏠 House/Real Estate</option>
                            <option value="🚗">🚗 Car/Vehicle</option>
                            <option value="✈️">✈️ Travel/Vacation</option>
                            <option value="💻">💻 Gadgets/Tech</option>
                            <option value="🎓">🎓 Education/Studies</option>
                            <option value="💍">💍 Wedding/Event</option>
                            <option value="🏥">🏥 Emergency Fund</option>
                            <option value="🏖️">🏖️ Retirement</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Color Theme</label>
                        <input type="color" name="color" value="#4F46E5" class="w-full h-10 bg-white border border-gray-700 rounded-lg p-1 cursor-pointer focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition">Create Goal</button>
                </div>
            </form>
        </x-modal>

        {{-- Filters --}}
        @include('savings.partials.filters')

        {{-- Savings Table/List --}}
        @include('savings.partials.history-table')
        
    </div>
</x-app-layout>
