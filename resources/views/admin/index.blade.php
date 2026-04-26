<x-app-layout>
    <div class="p-6 min-h-full">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white tracking-tight">Admin Panel</h1>
            <p class="text-xs text-gray-500 mt-1">Role-protected session overview</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-[#12121e] border border-white/10 rounded-xl p-5">
                <p class="text-xs uppercase tracking-widest text-gray-500">Users</p>
                <p class="text-3xl font-bold text-white mt-2">{{ number_format($totalUsers) }}</p>
            </div>
            <div class="bg-[#12121e] border border-white/10 rounded-xl p-5">
                <p class="text-xs uppercase tracking-widest text-gray-500">Savings Entries</p>
                <p class="text-3xl font-bold text-white mt-2">{{ number_format($totalSavingsEntries) }}</p>
            </div>
            <div class="bg-[#12121e] border border-white/10 rounded-xl p-5">
                <p class="text-xs uppercase tracking-widest text-gray-500">Goals</p>
                <p class="text-3xl font-bold text-white mt-2">{{ number_format($totalGoals) }}</p>
            </div>
            <div class="bg-[#12121e] border border-white/10 rounded-xl p-5">
                <p class="text-xs uppercase tracking-widest text-gray-500">Badges</p>
                <p class="text-3xl font-bold text-white mt-2">{{ number_format($totalBadges) }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
