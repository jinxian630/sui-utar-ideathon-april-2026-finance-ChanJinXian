<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>



    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div x-data="{ copied: false }">
            <x-input-label for="wallet_address" :value="__('Wallet Address (Sui)')" />
            <div class="flex items-center mt-1 gap-2">
                <x-text-input id="wallet_address" type="text" class="block w-full bg-gray-100 text-gray-500 font-mono text-sm" :value="$user->wallet_address ?? 'No wallet connected'" readonly />
                @if($user->wallet_address)
                <button type="button" 
                        @click="navigator.clipboard.writeText('{{ $user->wallet_address }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 h-11 w-28">
                    <span x-show="!copied">Copy</span>
                    <span x-cloak x-show="copied" class="text-green-400">Copied!</span>
                </button>
                @endif
            </div>
            <p class="mt-2 text-sm text-gray-500">Share this address with others so they can send you Sui tokens and NFTs.</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
