@props([
    'saved' => 1025,
    'target' => 5000,
    'nextBadge' => 'Diamond Saver',
    'gap' => 3975
])

@php
    $percentage = min(100, max(0, ($saved / $target) * 100));
    $circumference = 2 * pi() * 45; // r=45
    $offset = $circumference - ($percentage / 100) * $circumference;
@endphp

<div class="bg-[#12121E] border border-white/5 rounded-2xl p-6 flex flex-col items-center shadow-lg relative h-full justify-between">
    <div class="flex justify-between w-full items-center mb-2 text-xs font-medium uppercase tracking-wider text-gray-400">
        <span>Milestone</span>
        <span class="text-purple-400">{{ number_format($percentage, 0) }}%</span>
    </div>
    
    <div class="relative w-40 h-40 flex items-center justify-center my-2">
        <!-- Background Circle -->
        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="45" stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="none"></circle>
            
            <!-- Progress Circle -->
            <defs>
                <linearGradient id="purple-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#9333ea" />
                    <stop offset="100%" stop-color="#4f46e5" />
                </linearGradient>
            </defs>
            <circle cx="50" cy="50" r="45" stroke="url(#purple-gradient)" stroke-width="8" fill="none" 
                    stroke-dasharray="{{ $circumference }}" 
                    stroke-dashoffset="{{ $offset }}" 
                    stroke-linecap="round"
                    class="transition-all duration-1000 ease-out"
                    x-data="{ shown: false }" 
                    x-intersect="shown = true" 
                    :stroke-dashoffset="shown ? '{{ $offset }}' : '{{ $circumference }}'">
            </circle>
        </svg>
        
        <div class="absolute flex flex-col items-center">
            <span class="text-xs text-gray-400">Total Saved</span>
            <span class="text-xl font-bold text-white mt-1">RM {{ number_format($saved, 0) }}</span>
        </div>
    </div>
    
    <div class="text-center w-full mt-4 bg-white/5 py-3 rounded-xl border border-white/5">
        <p class="text-xs text-gray-500 mb-1">Next: {{ $nextBadge }}</p>
        <h4 class="text-base font-bold text-yellow-400 flex items-center justify-center gap-1">
            <span>💎</span> RM {{ number_format($target, 0) }}
        </h4>
        <div class="w-full bg-[#0D0D14] h-1.5 mt-3 rounded-full overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-500 h-full rounded-full" style="width: {{ $percentage }}%"></div>
        </div>
        <p class="text-[10px] text-gray-500 mt-2">RM {{ number_format($gap, 2) }} left</p>
    </div>
</div>
