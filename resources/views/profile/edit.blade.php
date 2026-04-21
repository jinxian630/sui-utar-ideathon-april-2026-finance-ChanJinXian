<x-app-layout>

    <div style="padding:1.5rem;min-height:100%;background:#0D0D14;">

        {{-- SUCCESS TOASTS --}}
        @if(session('status') === 'profile-updated')
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-cloak
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0 -translate-y-2"
                 style="position:fixed;top:1rem;right:1.5rem;z-index:999;display:flex;align-items:center;gap:0.75rem;background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.25);color:#4ade80;padding:0.75rem 1rem;border-radius:0.75rem;font-size:0.875rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                ✅ Profile updated successfully!
            </div>
        @endif

        @if(session('status') === 'password-updated')
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-cloak
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0 -translate-y-2"
                 style="position:fixed;top:1rem;right:1.5rem;z-index:999;display:flex;align-items:center;gap:0.75rem;background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.25);color:#4ade80;padding:0.75rem 1rem;border-radius:0.75rem;font-size:0.875rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                🔒 Password updated successfully!
            </div>
        @endif

        {{-- PAGE HEADER --}}
        <div style="margin-bottom:1.5rem;">
            <h1 style="color:white;font-size:1.5rem;font-weight:700;letter-spacing:-0.3px;">Profile Settings</h1>
            <p style="color:#5a5a7a;font-size:0.8rem;margin-top:0.25rem;">Manage your account information and security preferences</p>
        </div>

        {{-- TWO-COLUMN LAYOUT: left = profile + password, right = wallet + delete --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start;">

            {{-- LEFT COLUMN --}}
            <div style="display:flex;flex-direction:column;gap:1.25rem;">

                {{-- UPDATE PROFILE INFORMATION --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;background:rgba(124,92,255,0.06);border-radius:50%;pointer-events:none;"></div>
                    <div style="margin-bottom:1.25rem;">
                        <h2 style="color:white;font-size:1rem;font-weight:600;">Profile Information</h2>
                        <p style="color:#5a5a7a;font-size:0.75rem;margin-top:0.2rem;">Update your account's profile information and email address.</p>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" style="display:flex;flex-direction:column;gap:1rem;">
                        @csrf
                        @method('patch')

                        <div>
                            <label for="name" style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Name</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                                   style="width:100%;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;color:#e2e2f2;padding:0.625rem 0.875rem;font-size:0.875rem;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='rgba(124,92,255,0.6)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                            @error('name')
                                <p style="color:#f87171;font-size:0.7rem;margin-top:0.3rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Email Address</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                                   style="width:100%;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;color:#e2e2f2;padding:0.625rem 0.875rem;font-size:0.875rem;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='rgba(124,92,255,0.6)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                            @error('email')
                                <p style="color:#f87171;font-size:0.7rem;margin-top:0.3rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div style="display:flex;align-items:center;gap:1rem;">
                            <button type="submit"
                                    style="background:linear-gradient(135deg,#7C5CFF,#4f46e5);color:white;font-weight:600;border-radius:0.75rem;padding:0.625rem 1.25rem;font-size:0.875rem;border:none;cursor:pointer;box-shadow:0 4px 16px rgba(124,92,255,0.35);transition:opacity 0.2s;"
                                    onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- UPDATE PASSWORD --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <div style="margin-bottom:1.25rem;">
                        <h2 style="color:white;font-size:1rem;font-weight:600;">Update Password</h2>
                        <p style="color:#5a5a7a;font-size:0.75rem;margin-top:0.2rem;">Ensure your account is using a long, random password to stay secure.</p>
                    </div>

                    <form method="post" action="{{ route('password.update') }}" style="display:flex;flex-direction:column;gap:1rem;">
                        @csrf
                        @method('put')

                        <div>
                            <label for="update_password_current_password" style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Current Password</label>
                            <input id="update_password_current_password" type="password" name="current_password" autocomplete="current-password"
                                   style="width:100%;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;color:#e2e2f2;padding:0.625rem 0.875rem;font-size:0.875rem;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='rgba(124,92,255,0.6)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                            @error('current_password', 'updatePassword')
                                <p style="color:#f87171;font-size:0.7rem;margin-top:0.3rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="update_password_password" style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">New Password</label>
                            <input id="update_password_password" type="password" name="password" autocomplete="new-password"
                                   style="width:100%;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;color:#e2e2f2;padding:0.625rem 0.875rem;font-size:0.875rem;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='rgba(124,92,255,0.6)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                            @error('password', 'updatePassword')
                                <p style="color:#f87171;font-size:0.7rem;margin-top:0.3rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="update_password_password_confirmation" style="display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Confirm New Password</label>
                            <input id="update_password_password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                                   style="width:100%;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;color:#e2e2f2;padding:0.625rem 0.875rem;font-size:0.875rem;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='rgba(124,92,255,0.6)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                            @error('password_confirmation', 'updatePassword')
                                <p style="color:#f87171;font-size:0.7rem;margin-top:0.3rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit"
                                    style="background:linear-gradient(135deg,#7C5CFF,#4f46e5);color:white;font-weight:600;border-radius:0.75rem;padding:0.625rem 1.25rem;font-size:0.875rem;border:none;cursor:pointer;box-shadow:0 4px 16px rgba(124,92,255,0.35);transition:opacity 0.2s;"
                                    onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div style="display:flex;flex-direction:column;gap:1.25rem;">

                {{-- WALLET ADDRESS CARD --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <div style="margin-bottom:1.25rem;">
                        <h2 style="color:white;font-size:1rem;font-weight:600;">Sui Wallet Address</h2>
                        <p style="color:#5a5a7a;font-size:0.75rem;margin-top:0.2rem;">Share this address to receive Sui tokens and NFTs.</p>
                    </div>

                    @if($user->wallet_address)
                        <div style="background:#0a0a14;border:1px solid rgba(45,212,191,0.2);border-radius:0.875rem;padding:1rem;margin-bottom:1rem;">
                            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.625rem;">
                                <span style="width:8px;height:8px;border-radius:50%;background:#2dd4bf;display:inline-block;animation:pulse 2s infinite;"></span>
                                <span style="color:#2dd4bf;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;">Connected · Sui Testnet</span>
                            </div>
                            <p id="wallet-addr" style="color:#e2e2f2;font-family:monospace;font-size:0.8rem;word-break:break-all;line-height:1.6;">{{ $user->wallet_address }}</p>
                        </div>
                        <div x-data="{ copied: false }">
                            <button @click="navigator.clipboard.writeText('{{ $user->wallet_address }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    style="width:100%;background:rgba(45,212,191,0.1);border:1px solid rgba(45,212,191,0.25);color:#2dd4bf;font-weight:600;border-radius:0.75rem;padding:0.625rem;font-size:0.875rem;cursor:pointer;transition:all 0.2s;"
                                    onmouseover="this.style.background='rgba(45,212,191,0.18)'" onmouseout="this.style.background='rgba(45,212,191,0.1)'">
                                <span x-show="!copied">📋 Copy Wallet Address</span>
                                <span x-show="copied" x-cloak style="color:#4ade80;">✅ Copied to clipboard!</span>
                            </button>
                        </div>
                    @else
                        <div style="background:#0a0a14;border:1px dashed rgba(255,255,255,0.08);border-radius:0.875rem;padding:2rem;text-align:center;">
                            <p style="font-size:2rem;margin-bottom:0.5rem;">⛓️</p>
                            <p style="color:#5a5a7a;font-size:0.8rem;">No wallet connected</p>
                            <p style="color:#3a3a5a;font-size:0.7rem;margin-top:0.25rem;">Log in with Sui zkLogin to link your wallet</p>
                        </div>
                    @endif
                </div>

                {{-- ACCOUNT ROLE & INFO --}}
                <div style="background:#12121e;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);">
                    <h2 style="color:white;font-size:1rem;font-weight:600;margin-bottom:1rem;">Account Details</h2>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#0a0a14;border-radius:0.75rem;border:1px solid rgba(255,255,255,0.05);">
                            <span style="color:#6b6b8a;font-size:0.8rem;">Account Role</span>
                            <span style="color:white;font-size:0.8rem;font-weight:600;background:rgba(124,92,255,0.15);border:1px solid rgba(124,92,255,0.25);padding:0.2rem 0.75rem;border-radius:100px;text-transform:capitalize;">{{ $user->role ?? 'user' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#0a0a14;border-radius:0.75rem;border:1px solid rgba(255,255,255,0.05);">
                            <span style="color:#6b6b8a;font-size:0.8rem;">KYC Status</span>
                            @if(($user->kyc_status ?? '') === 'verified')
                                <span style="color:#4ade80;font-size:0.8rem;font-weight:600;background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.2);padding:0.2rem 0.75rem;border-radius:100px;">✅ Verified</span>
                            @else
                                <span style="color:#f59e0b;font-size:0.8rem;font-weight:600;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);padding:0.2rem 0.75rem;border-radius:100px;">⏳ Pending</span>
                            @endif
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#0a0a14;border-radius:0.75rem;border:1px solid rgba(255,255,255,0.05);">
                            <span style="color:#6b6b8a;font-size:0.8rem;">Member Since</span>
                            <span style="color:#e2e2f2;font-size:0.8rem;font-weight:500;">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- DELETE ACCOUNT --}}
                <div style="background:#12121e;border:1px solid rgba(248,113,113,0.15);border-radius:1rem;padding:1.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.4);" x-data="{ confirm: false }">
                    <div style="margin-bottom:1rem;">
                        <h2 style="color:#f87171;font-size:1rem;font-weight:600;">Danger Zone</h2>
                        <p style="color:#5a5a7a;font-size:0.75rem;margin-top:0.2rem;line-height:1.6;">Once your account is deleted, all data will be permanently removed. This action cannot be undone.</p>
                    </div>

                    <button @click="confirm = !confirm"
                            style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.25);color:#f87171;font-weight:600;border-radius:0.75rem;padding:0.625rem 1.25rem;font-size:0.875rem;cursor:pointer;transition:all 0.2s;"
                            onmouseover="this.style.background='rgba(248,113,113,0.2)'" onmouseout="this.style.background='rgba(248,113,113,0.1)'">
                        🗑️ Delete Account
                    </button>

                    <div x-show="confirm" x-cloak x-transition
                         style="margin-top:1rem;background:#0a0a14;border:1px solid rgba(248,113,113,0.2);border-radius:0.875rem;padding:1rem;">
                        <p style="color:#f87171;font-size:0.8rem;font-weight:600;margin-bottom:0.75rem;">⚠️ Type your password to confirm deletion:</p>
                        <form method="post" action="{{ route('profile.destroy') }}">
                            @csrf
                            @method('delete')
                            <input type="password" name="password" placeholder="Enter your password"
                                   style="width:100%;background:#12121e;border:1px solid rgba(248,113,113,0.3);border-radius:0.625rem;color:#e2e2f2;padding:0.5rem 0.75rem;font-size:0.875rem;outline:none;margin-bottom:0.75rem;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='rgba(248,113,113,0.7)'" onblur="this.style.borderColor='rgba(248,113,113,0.3)'">
                            @error('password', 'userDeletion')
                                <p style="color:#f87171;font-size:0.7rem;margin-bottom:0.5rem;">{{ $message }}</p>
                            @enderror
                            <div style="display:flex;gap:0.5rem;">
                                <button type="submit"
                                        style="flex:1;background:#ef4444;color:white;font-weight:600;border-radius:0.625rem;padding:0.5rem;font-size:0.8rem;border:none;cursor:pointer;transition:background 0.2s;"
                                        onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                                    Confirm Delete
                                </button>
                                <button type="button" @click="confirm = false"
                                        style="flex:1;background:rgba(255,255,255,0.05);color:#9a9ab0;border:1px solid rgba(255,255,255,0.08);border-radius:0.625rem;padding:0.5rem;font-size:0.8rem;cursor:pointer;transition:background 0.2s;"
                                        onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        {{-- Bottom spacer for scroll breathing room --}}
        <div style="height:2rem;"></div>
    </div>

    <style>
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
        [x-cloak] { display: none !important; }
    </style>

</x-app-layout>
