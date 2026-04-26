# Nuance Finance Tracker

Nuance is a Laravel finance tracker that combines traditional savings management with Sui Testnet wallet identity, milestone badges, goal deposits, wallet-linked access control, and a Gemini-powered AI financial assistant.

## Features

- Email/password authentication and Google Sui zkLogin onboarding.
- Web3-first registration with a 6-digit Nuance PIN flow.
- Wallet reveal page for first-time zkLogin users.
- Role-based access control using `users.role`.
- Admin-only dashboard at `/admin`.
- Wallet-linked middleware for Web3-sensitive actions.
- Savings entries, wallet balance, goals, withdrawals, and staking rebate logic.
- Sui Testnet sync for savings/profile metadata.
- Milestone badges with local records and Sui metadata links.
- Floating cyberpunk AI financial assistant using Gemini.
- Chat history persistence in `chat_logs`.

## Tech Stack

- Laravel 12, PHP 8.2
- Blade, Alpine.js, Tailwind/Vite
- MySQL or SQLite
- Sui Testnet SDK helpers
- Google zkLogin via `@mysten/sui`
- Gemini API using Laravel HTTP client

## Setup

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm install
```

Create the environment file:

```bash
copy .env.example .env
php artisan key:generate
```

Configure your database in `.env`. Example for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=asg1_user
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

Build frontend assets:

```bash
npm run build
```

Start Laravel:

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## Environment Variables

Gemini AI:

```env
GEMINI_API_KEY=
GEMINI_MODEL=gemini-3-flash-preview
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
```

Sui Testnet:

```env
SUI_RPC_URL=https://public-rpc.sui-testnet.mystenlabs.com
SUI_PACKAGE_ID=0x2dbc3443cb754187f7a4a3a4f9b7592f78e2f650f41cb0fd78a946ef422174d3
SUI_CLI_PATH=sui
SUI_CLI_ENV=testnet-direct
SUI_NODE_PATH=node
SUI_SYNC_DRIVER=sdk
SUI_GAS_BUDGET=10000000
SUI_SERVER_ADDRESS=
SUI_SERVER_SECRET_KEY=
SUI_VAULT_OBJECT_ID=
```

Session settings:

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

Do not commit real API keys, Sui private keys, or hot-wallet secrets.

## Authentication And Roles

The app uses Laravel web-session authentication.

Roles are stored in:

```text
users.role
```

Supported roles:

- `user`
- `admin`

User routes are protected with:

```text
auth + role:user,admin
```

Admin routes are protected with:

```text
auth + role:admin
```

Wallet-sensitive actions are protected with:

```text
wallet.linked
```

This requires the user to have `wallet_address` or `sui_address`.

## Admin Login

There is no separate admin login page. Admins use the normal login page.

To create or promote an admin in MySQL:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'admin@gmail.com';
```

Then login normally and visit:

```text
http://127.0.0.1:8000/admin
```

For local demo accounts, ensure the password is hashed with Laravel/PHP, not plain SQL text.

## zkLogin Registration Flow

The register page provides two paths:

- Sign up with Google zkLogin.
- Register with email/password.

For zkLogin:

1. User clicks Google zkLogin.
2. Browser starts Google OAuth with zkLogin nonce.
3. Google returns an ID token.
4. User sets a 6-digit Nuance PIN.
5. Browser derives a verifier from the PIN and identity data.
6. Laravel stores only a hash of the verifier in `zk_pin_hash`.
7. Laravel stores the generated Sui address in `wallet_address`.
8. First-time zkLogin users are redirected to `/wallet/welcome`.

The raw PIN is not sent to Laravel.

## Wallet Welcome

First-time zkLogin users are shown:

```text
/wallet/welcome
```

This page reveals the generated Sui wallet address and explains that Nuance has created a Sui Testnet vault for savings activity.

When the user continues, Laravel sets:

```text
users.wallet_onboarded_at
```

Returning onboarded zkLogin users go directly to the dashboard.

## AI Financial Assistant

The floating AI assistant uses Gemini through:

```text
POST /api/chat
```

The assistant injects user context into each request:

- Wallet and Sui profile status
- Goal deposit total
- Wallet balance
- Recent savings entries
- Recent transactions
- Current and next badge
- Goal progress
- Forecast metadata

Conversations are stored in:

```text
chat_logs
```

If `GEMINI_API_KEY` is missing or Gemini is unavailable, the app returns a local fallback response.

## Core Routes

Public:

- `GET /`
- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `POST /auth/zklogin`

Authenticated:

- `GET /dashboard`
- `GET /badges`
- `GET /savings`
- `GET /profile`
- `POST /api/chat`

Wallet-linked:

- `POST /savings`
- `PATCH /savings/{saving}`
- `DELETE /savings/{saving}`
- `POST /goals`
- `PATCH /goals/{goal}`
- `DELETE /goals/{goal}`
- `POST /goals/{goal}/withdraw`
- `POST /sui/profile`
- `POST /sui/savings/{entry}/mark-on-chain`
- `GET /wallet/welcome`
- `POST /wallet/welcome/complete`

Admin:

- `GET /admin`
- `GET /admin/analytics`

## Testing

Run all tests:

```bash
php artisan test
```

Run selected tests:

```bash
php artisan test --filter=SessionRbacTest
php artisan test --filter=ZkLoginOnboardingTest
php artisan test --filter=AiChatTest
```

Build frontend assets:

```bash
npm run build
```

The Vite build may warn that the zkLogin chunk is larger than 500 KB. This is expected because Sui/zkLogin libraries are bundled for the browser.

## Troubleshooting

Missing table errors:

```bash
php artisan migrate
```

Route/config/view cache issues:

```bash
php artisan optimize:clear
```

419 Page Expired:

- Refresh the login/register page.
- Clear cookies for `127.0.0.1`.
- Confirm forms include `@csrf`.
- Confirm `sessions` table exists if using `SESSION_DRIVER=database`.

403 Unauthorized on `/admin`:

- Confirm the logged-in user has `role = admin`.
- Log out and log in again after changing the role.

AI fallback response:

- Confirm `GEMINI_API_KEY` is set.
- Run `php artisan config:clear`.
- Confirm the model is `gemini-3-flash-preview`.

Wallet-linked redirect:

- Connect/sign in with a Sui wallet first.
- Ensure `users.wallet_address` or `users.sui_address` is not empty.

## Security Notes

- Session IDs are regenerated after password login and zkLogin.
- Logout invalidates the session and regenerates the CSRF token.
- Web3-sensitive routes require a linked wallet.
- Admin pages require `role = admin`.
- Raw Nuance PIN values are never stored.
- Do not expose Gemini or Sui secret keys in public repositories.
