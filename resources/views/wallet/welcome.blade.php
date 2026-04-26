<x-app-layout>
    <div class="min-h-full p-6 flex items-center justify-center">
        <div class="w-full max-w-3xl">
            <div class="mb-6">
                <p class="text-xs uppercase tracking-[0.25em] text-cyan-300 font-bold">Wallet Created</p>
                <h1 class="text-3xl font-bold text-white mt-2">Your Sui Testnet vault is ready</h1>
                <p class="text-gray-400 mt-2">Nuance created a blockchain vault for your savings. Future on-chain savings activity will be linked to this address.</p>
            </div>

            <div class="bg-[#10101D] border border-cyan-400/20 rounded-2xl p-6 shadow-2xl shadow-cyan-500/10">
                <div class="rounded-xl bg-gradient-to-br from-cyan-400/15 via-purple-500/15 to-fuchsia-500/15 border border-white/10 p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Sui Wallet Address</p>
                    <p id="wallet-address" class="text-lg md:text-xl text-white font-mono break-all leading-relaxed">{{ $walletAddress }}</p>
                    <button
                        type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('wallet-address').innerText)"
                        class="mt-4 inline-flex items-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-400/20">
                        Copy Address
                    </button>
                </div>

                <div class="grid md:grid-cols-3 gap-4 mt-5">
                    <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                        <p class="text-white font-semibold">1. Secure Identity</p>
                        <p class="text-sm text-gray-400 mt-1">Your Google sign-in created a zkLogin wallet without exposing a private key.</p>
                    </div>
                    <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                        <p class="text-white font-semibold">2. Nuance PIN</p>
                        <p class="text-sm text-gray-400 mt-1">Your PIN-derived verifier helps keep wallet generation unique to you.</p>
                    </div>
                    <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                        <p class="text-white font-semibold">3. Track Savings</p>
                        <p class="text-sm text-gray-400 mt-1">Deposits and badges can now connect to your Sui Testnet profile.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('wallet.welcome.complete') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-cyan-400 to-fuchsia-500 px-5 py-3 text-base font-bold text-white shadow-lg shadow-cyan-500/20">
                        Continue to Dashboard
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
