<x-app-layout>
<div class="max-w-2xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-bold text-white mb-2">Edit Savings Description</h1>
    <p class="text-sm text-gray-400 mb-6">
        Savings history is locked. You may update the description only.
    </p>

    <form method="POST" action="{{ route('savings.update', $saving) }}" class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-sm">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
            <textarea name="description" rows="4"
                      class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400">{{ old('description', $saving->description) }}</textarea>
            @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-700">
                <p class="text-sm text-gray-400">Amount</p>
                <p class="text-2xl font-bold text-gray-100">RM {{ number_format($saving->amount, 2) }}</p>
                @if($saving->round_up_amount > 0)
                    <p class="text-xs text-indigo-400 mt-1">
                        +RM {{ number_format($saving->round_up_amount, 2) }} smart round-up included
                    </p>
                @endif
            </div>
            <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-700">
                <p class="text-sm text-gray-400">Locked Details</p>
                <p class="text-sm text-gray-200 mt-1">{{ ucfirst($saving->type) }} - {{ optional($saving->entry_date ?? $saving->created_at)->format('d M Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $saving->goal ? $saving->goal->name : 'General' }}</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-500/30">
                Save Description
            </button>
            <a href="{{ route('savings.index') }}"
               class="flex-1 text-center bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold py-3 rounded-xl transition">
                Cancel
            </a>
        </div>
    </form>
</div>
</x-app-layout>
