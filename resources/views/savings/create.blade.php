<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Log a New Save') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700">
                <div class="p-6 text-gray-100">
                    <form method="POST" action="{{ route('savings.store') }}"
                          x-data="{
                              amount: '',
                              roundUp: false,
                              stake: false,
                              get roundUpPreview() {
                                  if (!this.roundUp || !this.amount) return 0;
                                  return (Math.ceil(parseFloat(this.amount) / 10) * 10 - parseFloat(this.amount)).toFixed(2);
                              }
                          }">
                        @csrf

                        {{-- Amount --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Amount (RM)</label>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                   x-model="amount"
                                   class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400"
                                   placeholder="0.00" required>
                            @error('amount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Type --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Type</label>
                            <select name="type" class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="income">Income / Savings</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>

                        {{-- Goal --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Assign to Goal</label>
                            <select name="goal_id" required class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                <option value="">-- Select a Goal --</option>
                                @foreach($goals as $g)
                                    <option value="{{ $g->id }}">{{ $g->emoji }} {{ $g->name }} (Target: RM {{ number_format($g->target_amount) }})</option>
                                @endforeach
                            </select>
                            @error('goal_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Note --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Note</label>
                            <input type="text" name="note" maxlength="255"
                                   class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400"
                                   placeholder="e.g. Salary, grocery run…">
                            @error('note') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Entry Date --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Date</label>
                            <input type="date" name="entry_date"
                                   value="{{ old('entry_date', today()->toDateString()) }}"
                                   max="{{ today()->toDateString() }}"
                                   class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Smart Round-Up Toggle --}}
                        <div class="mb-4 p-4 bg-indigo-900/30 rounded-xl border border-indigo-500/50">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="enable_round_up" value="1" x-model="roundUp"
                                       class="w-4 h-4 bg-gray-900 border-gray-600 rounded text-indigo-600 focus:ring-indigo-500 focus:ring-offset-gray-800">
                                <div>
                                    <p class="text-sm font-semibold text-indigo-300">🔄 Smart Round-Up</p>
                                    <p class="text-xs text-indigo-400">Round your entry up to the nearest RM 10</p>
                                </div>
                            </label>
                            <p x-show="roundUp && parseFloat(roundUpPreview) > 0"
                               class="mt-3 text-sm text-indigo-300 font-medium bg-indigo-900/50 p-2 rounded border border-indigo-500/30">
                                RM <span x-text="roundUpPreview"></span> spare change will be added automatically
                            </p>
                        </div>

                        {{-- Staking Service Toggle --}}
                        <div class="mb-6 p-4 bg-emerald-900/30 rounded-xl border border-emerald-500/50">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="enable_stake" value="1" x-model="stake"
                                       class="mt-1 w-4 h-4 bg-gray-900 border-gray-600 rounded text-emerald-600 focus:ring-emerald-500 focus:ring-offset-gray-800">
                                <div>
                                    <p class="text-sm font-semibold text-emerald-300">Staking Service</p>
                                    <p class="text-xs text-emerald-400 leading-relaxed">
                                        Auto-stake this savings entry into the Sui vault after it is logged.
                                    </p>
                                    <p class="mt-2 text-xs text-emerald-200/80 leading-relaxed">
                                        Milestone rebate rewards start at 1.0% and can reach 5.0%, with a 1.2x stacking bonus when multiple milestones unlock in the same month.
                                    </p>
                                </div>
                            </label>
                            <p x-show="stake"
                               x-cloak
                               class="mt-3 text-sm text-emerald-200 font-medium bg-emerald-950/60 p-3 rounded-lg border border-emerald-500/30">
                                This deposit will be queued for Sui vault staking and can be verified from Savings History.
                            </p>
                        </div>

                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-500/30">
                            Log Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
