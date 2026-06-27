# 🏡 HomesMenu

A full-stack real-estate marketplace (99acres / MagicBricks class) with a built-in
**Refer & Earn** rewards program, **360° virtual tours**, **Google Sign-In + Maps**,
and three roles — **Buyer / Seller / Dealer**.

- **Backend** — Laravel 12, headless JSON API (Sanctum bearer tokens)
- **Frontend** — Next.js 16 (App Router, TypeScript, Tailwind v4)
- **Database** — MySQL 8
- **360° tours** — Photo Sphere Viewer (equirectangular room panoramas)
- **File uploads** — local disk now, **S3-ready** (one env switch)
- Theme — white + forest green

```
HomesMenu/
├── backend/            Laravel API
├── frontend/           Next.js app
└── docker-compose.yml  MySQL + API + frontend
```

## 🚀 Run it (Docker — recommended)

You only need Docker Desktop.

```bash
cd HomesMenu
docker compose up --build
```

First boot installs deps, runs migrations and seeds demo data (~1–2 min). Then:

| Service | URL |
|---------|-----|
| Frontend | http://localhost:3000 |
| API | http://localhost:8001/api |
| MySQL | localhost:3307 · db `homesmenu` · user `homesmenu` / `secret` |

Stop with `Ctrl+C`. Reset everything (including the DB): `docker compose down -v`.

### Demo accounts (password: `password`)
| Role | Email | Lands on |
|------|-------|----------|
| Buyer | `buyer@homesmenu.test` | `/dashboard/buyer` |
| Seller | `seller@homesmenu.test` | `/dashboard/seller` |
| Dealer | `dealer@homesmenu.test` | `/dashboard/dealer` |
| Super Admin | `admin@homesmenu.test` | `/admin` |

### Listing moderation & multi-dealer flow
- **Dealers/sellers** add a property → it stays **pending** (draft) until the
  **super-admin team** reviews & enhances it at **`/admin/properties`** and approves it.
  Admin's own listings publish immediately.
- The **super admin** can also post properties; those appear under a dealer's
  **"Available to Sell"** (`/dashboard/available`). A dealer **opts in with their
  contact details** and can then also sell that property.
- On the **public property page**, buyers see **every dealer** listed on the property
  (name, phone, email) so they can contact any of them.
- Demo seed: 2 listings sit in the admin **review queue**; the dealer demo account has
  admin inventory available to pick up and some already-joined listings.

## 🧑‍💻 Local dev (optional, faster frontend reload)

The frontend container runs a production build. For hot-reload, run it locally
(Node 20+ is enough) while the API + DB stay in Docker:

```bash
docker compose up db backend         # API on :8000
cd frontend && npm install && npm run dev   # app on :3000
```

The backend needs PHP 8.2+, Composer & MySQL only if you want to run it *outside*
Docker (`composer install && php artisan migrate --seed && php artisan serve`).

## 🔑 Google integration

Both are optional — the app runs without them (Maps shows a styled placeholder;
email/password login still works).

1. Create an OAuth 2.0 Client + a Maps Embed API key at
   <https://console.cloud.google.com>.
2. **Backend** — set in `backend/.env`:
   ```
   GOOGLE_CLIENT_ID=...
   GOOGLE_CLIENT_SECRET=...
   GOOGLE_REDIRECT_URI=http://localhost:8001/api/auth/google/callback
   ```
   Add that redirect URI to the OAuth client's *Authorized redirect URIs*.
3. **Frontend** — set in `frontend/.env.local`:
   ```
   NEXT_PUBLIC_GOOGLE_MAPS_EMBED_KEY=...
   ```
4. Restart. "Continue with Google" → Google → back to the app, signed in.

## 🗂️ File uploads → S3 later

Uploads go to the local `public` disk now (served at `/storage/...`). To switch to
S3, set in `backend/.env`:
```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=...
```
No code changes — uploads, property images and panoramas all resolve their URLs via
`Storage::disk()`.

## 🖼️ Sample images

All seeded photos are **license-clear placeholders** (Lorem Picsum + free sample
equirectangular panoramas) — nothing is scraped from any third party. Replace them
with real photos through the in-app **Add Property** upload flow (or your own seeder).

## 🔌 API overview (selected)

Public: `GET /api/properties` (filters: q, city, property_type, bedrooms, min/max_price,
verified, has_tour, sort) · `GET /api/properties/{slug}` · `GET /api/properties/{slug}/panoramas`
· `POST /api/properties/{slug}/enquire` · `POST /api/properties/{slug}/site-visits`
· `GET /api/meta` · `POST /api/auth/register|login` · `GET /api/auth/google/redirect`.

Authenticated (Bearer): `GET /api/me` · `GET/PUT /api/profile` · `GET /api/saved`
+ `POST/DELETE /api/saved/{slug}` · `GET /api/my/properties` · `POST /api/properties`
· `POST /api/uploads` · `GET /api/leads` · `GET /api/referrals` + `POST /api/referrals`
· `GET /api/wallet` + `POST /api/wallet/withdraw` · `GET /api/dashboard/{buyer|seller|dealer}`.

## 🧱 Data model

`users` (roles, referral_code, wallet_balance) · `properties` (+ `property_images`,
`panorama_scenes`) · `saved_properties` · `leads` · `site_visits` · `referrals`
(tiered rewards) · `transactions` (wallet ledger). See `backend/database/migrations`.

---
Built with Laravel + Next.js. Design spec for the original prototype lives in
`../memories-md`.
