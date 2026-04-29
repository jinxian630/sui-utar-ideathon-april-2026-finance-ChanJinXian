<x-app-layout>
    <div style="padding:1.5rem;min-height:100%;background:#0D0D14;">
        @if(session('status') === 'profile-updated' || session('status') === 'pin-updated')
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-cloak
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0 -translate-y-2"
                 style="position:fixed;top:1rem;right:1.5rem;z-index:999;display:flex;align-items:center;gap:0.75rem;background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.25);color:#4ade80;padding:0.75rem 1rem;border-radius:0.75rem;font-size:0.875rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                {{ session('status') === 'pin-updated' ? 'Nuance PIN updated successfully.' : 'Profile updated successfully.' }}
            </div>
        @endif

        <div style="margin-bottom:1.5rem;">
            <h1 style="color:white;font-size:1.5rem;font-weight:700;letter-spacing:0;">Profile Settings</h1>
            <p style="color:#5a5a7a;font-size:0.8rem;margin-top:0.25rem;">Manage your profile, Google zkLogin identity, and security PIN.</p>
        </div>

        <div class="profile-grid" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:1.25rem;align-items:start;">
            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                <section class="settings-card">
                    <div style="margin-bottom:1.25rem;">
                        <h2 class="settings-title">Profile Information</h2>
                        <p class="settings-copy">Update your display name. Your Google email stays locked to your zkLogin identity.</p>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" style="display:flex;flex-direction:column;gap:1rem;">
                        @csrf
                        @method('patch')

                        <div>
                            <label for="name" class="settings-label">Name</label>
                            <input id="name" class="settings-input" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                            @error('name')
                                <p class="settings-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <span class="settings-label">Email Address</span>
                            <div class="readonly-field">{{ $user->email }}</div>
                            <p class="settings-hint">Email cannot be changed because it is linked to your Google zkLogin identity.</p>
                        </div>

                        <div>
                            <button type="submit" class="primary-action">Save Changes</button>
                        </div>
                    </form>
                </section>

                <section class="settings-card">
                    <div style="margin-bottom:1.25rem;">
                        <h2 class="settings-title">Security PIN</h2>
                        <p class="settings-copy">Change the 6-digit Nuance PIN used to protect your zkLogin wallet identity.</p>
                    </div>

                    <form method="post" action="{{ route('profile.pin.update') }}" style="display:flex;flex-direction:column;gap:1rem;">
                        @csrf
                        @method('patch')

                        <div>
                            <label for="current_pin" class="settings-label">Current PIN</label>
                            <input id="current_pin" class="settings-input pin-input" type="password" name="current_pin" inputmode="numeric" maxlength="6" autocomplete="current-password" placeholder="6-digit PIN">
                            @error('current_pin', 'updatePin')
                                <p class="settings-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_pin" class="settings-label">New PIN</label>
                            <input id="new_pin" class="settings-input pin-input" type="password" name="new_pin" inputmode="numeric" maxlength="6" autocomplete="new-password" placeholder="New 6-digit PIN">
                            @error('new_pin', 'updatePin')
                                <p class="settings-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_pin_confirmation" class="settings-label">Confirm New PIN</label>
                            <input id="new_pin_confirmation" class="settings-input pin-input" type="password" name="new_pin_confirmation" inputmode="numeric" maxlength="6" autocomplete="new-password" placeholder="Repeat new PIN">
                            @error('new_pin_confirmation', 'updatePin')
                                <p class="settings-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="primary-action">Update PIN</button>
                        </div>
                    </form>
                </section>
            </div>

            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                <section class="settings-card">
                    <div style="margin-bottom:1.25rem;">
                        <h2 class="settings-title">Sui Wallet Address</h2>
                        <p class="settings-copy">Share this address to receive Sui tokens and NFTs.</p>
                    </div>

                    @if($user->wallet_address)
                        <div style="background:#0a0a14;border:1px solid rgba(45,212,191,0.2);border-radius:0.875rem;padding:1rem;margin-bottom:1rem;">
                            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.625rem;">
                                <span style="width:8px;height:8px;border-radius:50%;background:#2dd4bf;display:inline-block;animation:pulse 2s infinite;"></span>
                                <span style="color:#2dd4bf;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;">Connected - Sui Testnet</span>
                            </div>
                            <p id="wallet-addr" style="color:#e2e2f2;font-family:monospace;font-size:0.8rem;word-break:break-all;line-height:1.6;">{{ $user->wallet_address }}</p>
                        </div>
                        <div x-data="{ copied: false }">
                            <button @click="navigator.clipboard.writeText('{{ $user->wallet_address }}'); copied = true; setTimeout(() => copied = false, 2500)" class="secondary-action">
                                <span x-show="!copied">Copy Wallet Address</span>
                                <span x-show="copied" x-cloak style="color:#4ade80;">Copied to clipboard.</span>
                            </button>
                        </div>
                    @else
                        <div style="background:#0a0a14;border:1px dashed rgba(255,255,255,0.08);border-radius:0.875rem;padding:2rem;text-align:center;">
                            <p style="color:#5a5a7a;font-size:0.8rem;">No wallet connected</p>
                            <p style="color:#3a3a5a;font-size:0.7rem;margin-top:0.25rem;">Log in with Sui zkLogin to link your wallet</p>
                        </div>
                    @endif
                </section>

                <section class="settings-card">
                    <h2 class="settings-title" style="margin-bottom:1rem;">Account Details</h2>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        <div class="detail-row">
                            <span>Account Role</span>
                            <strong>{{ $user->role ?? 'user' }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>KYC Status</span>
                            <strong>{{ ($user->kyc_status ?? '') === 'verified' ? 'Verified' : 'Pending' }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Member Since</span>
                            <strong>{{ $user->created_at->format('d M Y') }}</strong>
                        </div>
                    </div>
                </section>

                <section class="settings-card danger-card"
                         x-data="deleteAccountFlow('{{ route('profile.pin.verify') }}')">
                    <div style="margin-bottom:1rem;">
                        <h2 style="color:#f87171;font-size:1rem;font-weight:600;">Danger Zone</h2>
                        <p class="settings-copy">Verify your Nuance PIN first. A final confirmation appears only after the PIN is correct.</p>
                    </div>

                    <div x-show="!verified" x-cloak>
                        <label for="delete_pin" class="settings-label">Nuance PIN</label>
                        <input id="delete_pin" x-model="pin" class="settings-input pin-input danger-input" type="password" inputmode="numeric" maxlength="6" placeholder="Enter your PIN">
                        <p x-show="error" x-text="error" class="settings-error" x-cloak></p>
                        @error('zk_pin', 'userDeletion')
                            <p class="settings-error">{{ $message }}</p>
                        @enderror
                        <button type="button" @click="verify" class="danger-action" style="margin-top:0.75rem;" :disabled="loading">
                            <span x-text="loading ? 'Checking PIN...' : 'Verify PIN'"></span>
                        </button>
                    </div>

                    <div x-show="verified" x-cloak x-transition class="confirm-panel">
                        <h3 style="color:white;font-size:0.95rem;font-weight:700;margin:0 0 0.35rem;">Confirm account deletion</h3>
                        <p style="color:#fca5a5;font-size:0.75rem;line-height:1.6;margin:0 0 1rem;">Your PIN is correct. Deleting this account permanently removes your profile, wallet link, savings entries, goals, badges, and chat history.</p>
                        <form method="post" action="{{ route('profile.destroy') }}">
                            @csrf
                            @method('delete')
                            <input type="hidden" name="zk_pin" :value="pin">
                            <div style="display:flex;gap:0.5rem;">
                                <button type="submit" class="danger-action" style="flex:1;">Delete Account</button>
                                <button type="button" @click="reset" class="cancel-action" style="flex:1;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div style="height:2rem;"></div>
    </div>

    <script>
        function deleteAccountFlow(verifyUrl) {
            return {
                pin: '',
                error: '',
                loading: false,
                verified: false,
                async verify() {
                    this.error = '';

                    if (!/^\d{6}$/.test(this.pin)) {
                        this.error = 'Enter exactly 6 digits.';
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch(verifyUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            },
                            body: JSON.stringify({ zk_pin: this.pin }),
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || !data.verified) {
                            this.error = data.message || data.errors?.zk_pin?.[0] || 'The Nuance PIN is incorrect.';
                            return;
                        }

                        this.verified = true;
                    } finally {
                        this.loading = false;
                    }
                },
                reset() {
                    this.pin = '';
                    this.error = '';
                    this.verified = false;
                    this.loading = false;
                },
            };
        }
    </script>

    <style>
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
        [x-cloak] { display: none !important; }

        .settings-card {
            background: #12121e;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
        }

        .danger-card {
            border-color: rgba(248,113,113,0.15);
        }

        .settings-title {
            color: white;
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }

        .settings-copy,
        .settings-hint {
            color: #5a5a7a;
            font-size: 0.75rem;
            line-height: 1.6;
            margin: 0.2rem 0 0;
        }

        .settings-hint {
            color: #8a8aa3;
            margin-top: 0.45rem;
        }

        .settings-label {
            display: block;
            font-size: 0.65rem;
            color: #6b6b8a;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.4rem;
        }

        .settings-input,
        .readonly-field {
            width: 100%;
            background: #0a0a14;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 0.75rem;
            color: #e2e2f2;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            outline: none;
            box-sizing: border-box;
        }

        .settings-input:focus {
            border-color: rgba(124,92,255,0.6);
            box-shadow: 0 0 0 3px rgba(124,92,255,0.12);
        }

        .pin-input {
            letter-spacing: 0.16em;
        }

        .danger-input:focus {
            border-color: rgba(248,113,113,0.7);
            box-shadow: 0 0 0 3px rgba(248,113,113,0.12);
        }

        .readonly-field {
            color: #9a9ab0;
            border-style: dashed;
        }

        .settings-error {
            color: #f87171;
            font-size: 0.7rem;
            margin: 0.4rem 0 0;
        }

        .primary-action,
        .secondary-action,
        .danger-action,
        .cancel-action {
            border: none;
            border-radius: 0.75rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, background 0.2s;
        }

        .primary-action {
            background: linear-gradient(135deg,#7C5CFF,#4f46e5);
            color: white;
            box-shadow: 0 4px 16px rgba(124,92,255,0.35);
        }

        .secondary-action {
            width: 100%;
            background: rgba(45,212,191,0.1);
            border: 1px solid rgba(45,212,191,0.25);
            color: #2dd4bf;
        }

        .danger-action {
            background: #ef4444;
            color: white;
        }

        .danger-action:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .cancel-action {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            color: #9a9ab0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: #0a0a14;
            border-radius: 0.75rem;
            border: 1px solid rgba(255,255,255,0.05);
            color: #6b6b8a;
            font-size: 0.8rem;
        }

        .detail-row strong {
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .confirm-panel {
            background: #0a0a14;
            border: 1px solid rgba(248,113,113,0.2);
            border-radius: 0.875rem;
            padding: 1rem;
        }

        @media (max-width: 900px) {
            .profile-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</x-app-layout>
