{{-- resources/views/savings/partials/filters.blade.php --}}

{{-- ── Preset Quick-Filter Pills ──────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-2 mb-4">
    @php
        $presets = [
            null         => 'All Time',
            'this_month' => 'This Month',
            'pending'    => 'Pending',
            'settled'    => 'Settled ⛓',
        ];
    @endphp
    @foreach($presets as $val => $label)
        @php
            $active = request('preset') === $val || ($val === null && !request('preset'));
            $color  = match($val) {
                'pending' => $active
                    ? 'bg-amber-500/20 text-amber-300 border-amber-500/40 shadow-[0_0_12px_rgba(245,158,11,0.2)]'
                    : 'text-gray-500 border-white/8 hover:border-amber-400/30 hover:text-amber-300',
                'settled' => $active
                    ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 shadow-[0_0_12px_rgba(16,185,129,0.2)]'
                    : 'text-gray-500 border-white/8 hover:border-emerald-400/30 hover:text-emerald-300',
                default   => $active
                    ? 'bg-indigo-600 text-white border-indigo-500 shadow-[0_0_12px_rgba(79,70,229,0.3)]'
                    : 'text-gray-500 border-white/8 hover:bg-white/5 hover:text-white',
            };
        @endphp
        <a href="{{ route('savings.index', $val ? ['preset' => $val] : []) }}"
           class="px-4 py-1.5 rounded-full text-xs font-bold border transition {{ $color }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- ── Detailed Filters Row ────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('savings.index') }}"
      class="flex flex-wrap items-center gap-3 p-3 bg-[#12121e] border border-white/5 rounded-2xl shadow-sm mb-6">
    @if(request('preset'))<input type="hidden" name="preset" value="{{ request('preset') }}">@endif

    {{-- Type filter --}}
    <div class="flex items-center gap-2">
        <label class="text-[10px] uppercase tracking-widest text-gray-600 font-bold whitespace-nowrap">Type</label>
        <select name="type"
                class="bg-white border border-gray-700 text-gray-900 text-xs rounded-lg px-3 py-1.5
                       focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none cursor-pointer">
            <option value="">All</option>
            <option value="income" @selected(request('type')==='income')>Income</option>
            <option value="expense" @selected(request('type')==='expense')>Expense</option>
        </select>
    </div>

    <div class="w-px h-5 bg-white/8"></div>

    {{-- Goal filter --}}
    <div class="flex items-center gap-2">
        <label class="text-[10px] uppercase tracking-widest text-gray-600 font-bold whitespace-nowrap">Goal</label>
        <select name="goal_id"
                class="bg-white border border-gray-700 text-gray-900 text-xs rounded-lg px-3 py-1.5
                       focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none cursor-pointer max-w-[130px] truncate">
            <option value="">All Goals</option>
            @foreach($goals as $goal)
                <option value="{{ $goal->id }}" @selected(request('goal_id')==$goal->id)>
                    {{ $goal->emoji }} {{ $goal->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="w-px h-5 bg-white/8"></div>

    {{-- Date range --}}
    <div class="flex items-center gap-2">
        <label class="text-[10px] uppercase tracking-widest text-gray-600 font-bold">From</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="bg-white border border-gray-700 text-gray-900 text-xs rounded-lg px-3 py-1.5
                      focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
    </div>
    <div class="flex items-center gap-2">
        <label class="text-[10px] uppercase tracking-widest text-gray-600 font-bold">To</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="bg-white border border-gray-700 text-gray-900 text-xs rounded-lg px-3 py-1.5
                      focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
    </div>

    {{-- Actions --}}
    <button type="submit"
            class="ml-auto px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold
                   rounded-xl transition shadow-sm">
        Apply
    </button>
    <a href="{{ route('savings.index') }}"
       class="px-4 py-1.5 bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs font-semibold
              rounded-xl border border-white/8 transition">
        Reset
    </a>
</form>
