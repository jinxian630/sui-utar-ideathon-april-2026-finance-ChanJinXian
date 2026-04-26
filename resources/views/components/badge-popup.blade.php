@props([
    'walletAddress' => '',
    'packageId' => '',
])

<div
    id="badge-event-listener"
    data-wallet-address="{{ strtolower($walletAddress ?? '') }}"
    data-package-id="{{ $packageId }}"
    data-rpc-url="{{ config('sui.rpc_url') }}"
    x-data="{
        open: false,
        badgeName: '',
        badgeLevel: '',
        suiLink: '',
        badgeImage: '',
        shareLink: '',
        walletLink: 'https://testnet.suivision.xyz/account/{{ strtolower($walletAddress ?? '') }}',
        viewLink: '',
        showBadge(event) {
            this.badgeName = event.detail.name || 'Finance Milestone';
            this.badgeLevel = event.detail.level || '';
            this.suiLink = event.detail.link || '';
            this.badgeImage = event.detail.imageUrl || '';
            this.viewLink = this.suiLink || this.walletLink;
            this.shareLink = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(this.viewLink || window.location.origin);
            this.open = true;
        }
    }"
    x-on:badge-earned.window="showBadge($event)"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 px-4 py-8 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        aria-labelledby="badge-earned-title"
    >
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            @for ($i = 0; $i < 28; $i++)
                <span
                    class="badge-confetti"
                    style="
                        left: {{ ($i * 37) % 100 }}%;
                        animation-delay: {{ ($i % 9) * 0.14 }}s;
                        background: {{ ['#facc15', '#38bdf8', '#a78bfa', '#34d399', '#fb7185'][$i % 5] }};
                    "
                ></span>
            @endfor
        </div>

        <div
            x-show="open"
            x-transition.scale.origin.center
            class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-white/15 bg-[#101024]/95 p-6 text-center shadow-2xl shadow-violet-950/50"
        >
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-400 via-violet-400 to-fuchsia-400"></div>

            <button
                type="button"
                x-on:click="open = false"
                class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-gray-300 transition hover:bg-white/10 hover:text-white"
                aria-label="Close badge celebration"
            >
                &times;
            </button>

            <p class="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-cyan-200">On-chain badge minted</p>
            <h2 id="badge-earned-title" class="mb-3 text-3xl font-black tracking-tight text-white">Congratulations!</h2>
            <p class="mx-auto mb-6 max-w-sm text-sm leading-6 text-indigo-100">
                You've unlocked the
                <span x-text="badgeName" class="font-bold text-white"></span>
                Badge NFT on Sui Testnet.
            </p>

            <div class="mx-auto mb-6 flex h-44 w-44 items-center justify-center rounded-2xl border border-white/15 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-cyan-400/20 p-3 shadow-[0_0_45px_rgba(124,92,255,0.28)]">
                <div class="relative flex h-full w-full animate-badge-float items-center justify-center rounded-xl bg-[#17172e]">
                    <img
                        x-show="badgeImage"
                        :src="badgeImage"
                        alt="Badge NFT preview"
                        class="absolute inset-0 h-full w-full rounded-xl object-cover"
                        x-on:error="$el.style.display = 'none'"
                    >
                    <div class="flex h-28 w-28 items-center justify-center rounded-full border border-white/20 bg-gradient-to-br from-amber-300 via-violet-400 to-cyan-300 text-4xl font-black text-[#111126] shadow-xl">
                        <span x-text="badgeLevel ? 'L' + badgeLevel : 'NFT'"></span>
                    </div>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 text-left">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Sui object</p>
                <p
                    class="mt-1 truncate font-mono text-xs text-indigo-100"
                    x-text="suiLink ? suiLink.replace('https://testnet.suivision.xyz/object/', '') : 'Badge NFT mint pending; opening wallet on SuiVision'"
                ></p>
            </div>

            <div class="flex flex-col justify-center gap-3 sm:flex-row">
                <button
                    type="button"
                    x-on:click="open = false"
                    class="rounded-lg border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-bold text-gray-200 transition hover:bg-white/10"
                >
                    Dismiss
                </button>
                <a
                    :href="viewLink"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-400"
                >
                    <span x-text="suiLink ? 'View on SuiVision' : 'View Wallet on SuiVision'"></span>
                </a>
                <a
                    :href="shareLink"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="rounded-lg bg-indigo-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-400"
                >
                    Share to Facebook
                </a>
            </div>
        </div>
    </div>

    <style>
        .badge-confetti {
            position: absolute;
            top: -18px;
            width: 9px;
            height: 14px;
            border-radius: 3px;
            opacity: 0.9;
            animation: badge-confetti-fall 2.8s linear infinite;
        }

        @keyframes badge-confetti-fall {
            0% { transform: translate3d(0, -20px, 0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translate3d(32px, 110vh, 0) rotate(720deg); opacity: 0; }
        }

        @keyframes badge-float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-8px) scale(1.03); }
        }

        .animate-badge-float {
            animation: badge-float 1.8s ease-in-out infinite;
        }
    </style>

    @if(session()->has('badge_earned_payloads'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const badges = @json(session('badge_earned_payloads'));
                badges.forEach((badge, index) => {
                    window.setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('badge-earned', { detail: badge }));
                    }, index * 1200);
                });
            });
        </script>
    @endif
</div>
