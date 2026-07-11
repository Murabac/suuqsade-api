# Suuqsade — Developer Context (Session Memory)

> **Purpose:** Living handoff doc for AI + developers. Captures environment, progress, fixes, and how to run **both** codebases.
>
> **Product spec** (features, API, schema, branding) → see [`CONTEXT.md`](./CONTEXT.md)
>
> **Last updated:** 2026-07-11

---

## 1. Repositories

| Project | Path | Stack | Git |
|---------|------|-------|-----|
| **API + Admin** | `c:\Users\lappybooks\MurabacApps\Suuqsade-API` | Laravel 13, PHP 8.3, Sanctum, Livewire, MySQL | `main` @ `Murabac/suuqsade-api` — **Week 4 work uncommitted** |
| **Customer app** | `c:\Users\lappybooks\MurabacApps\Suuqsade-App` | Flutter 3.41+, Riverpod, Dio | Not a git repo yet |
| **Planning / brand** | `MurabacApps/Suuqsade/` | CONTEXT.md + brand-assets | — |

**GitHub (API):** `git@github.com:Murabac/suuqsade-api.git`  
**Last pushed commit:** `0bf5786` — Week 2 (order API + admin Livewire)

---

## 2. Local environment (Windows)

### PHP 8.3 (portable — not on system PATH by default)

```
C:\Users\lappybooks\AppData\Local\Programs\PHP\8.3\php.exe
C:\Users\lappybooks\AppData\Local\Programs\PHP\8.3\composer.phar
```

**php.ini fix applied:** `pdo_sqlite` and `sqlite3` enabled (required for `php artisan test`).

### MySQL (XAMPP 8.2)

```
Host:     127.0.0.1
Database: suuqsade
User:     root
Password: (empty)
Path:     C:\xampp
```

### Flutter

```
Version: 3.41.6
Physical device: SM S918B (id: R5CW318NCLD, Android 16)
```

### PC LAN IP (for phone testing — **re-check with `ipconfig` if Wi‑Fi changes**)

```
192.168.8.111
```

---

## 3. How to run everything

### API + Admin (must bind to all interfaces for phone)

```powershell
$php = "$env:LOCALAPPDATA\Programs\PHP\8.3\php.exe"
cd c:\Users\lappybooks\MurabacApps\Suuqsade-API
& $php artisan serve --host=0.0.0.0 --port=8000
```

| URL | Use |
|-----|-----|
| http://127.0.0.1:8000 | Browser on PC |
| http://192.168.8.111:8000 | Phone browser / Flutter app |
| http://127.0.0.1:8000/admin | Admin dashboard |

**Admin login:** `admin@suuqsade.com` / `password`

**Do NOT** use `php artisan serve` without `--host=0.0.0.0` when testing on a physical phone — it only listens on `127.0.0.1`.

### Flutter app

```powershell
cd c:\Users\lappybooks\MurabacApps\Suuqsade-App
flutter pub get

# Physical phone (update IP if needed)
flutter run -d R5CW318NCLD --dart-define=API_BASE_URL=http://192.168.8.111:8000

# Android emulator
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000

# Windows desktop
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000
```

### Tests (API)

```powershell
cd c:\Users\lappybooks\MurabacApps\Suuqsade-API
& "$env:LOCALAPPDATA\Programs\PHP\8.3\php.exe" artisan test
```

**Status:** 19 tests passing (auth, order E2E, admin Livewire).

### Windows Firewall (if phone can't reach API)

Run **PowerShell as Administrator**:

```powershell
netsh advfirewall firewall add rule name="Suuqsade API 8000" dir=in action=allow protocol=TCP localport=8000
```

---

## 4. Auth & config

### Laravel `.env` (key values)

```env
DB_CONNECTION=mysql
DB_DATABASE=suuqsade
OTP_FIXED_CODE=123456
OTP_BYPASS=true
# FCM_SERVER_KEY=...   # not set — push disabled
```

### App auth (MVP)

- Any phone number on login screen
- OTP code: **`123456`**
- Sanctum token stored on device

### API base URL (Flutter)

Defined in `Suuqsade-App/lib/config/api_config.dart` — override with `--dart-define=API_BASE_URL=...`

---

## 5. Build progress

| Week | Focus | Status |
|------|-------|--------|
| 1 | Laravel migrations + auth; Flutter scaffold | ✅ Done |
| 2 | Order API + admin Livewire | ✅ Done & pushed |
| 3 | Flutter core screens + FCM stub | ✅ Done |
| 4 | Admin polish + E2E tests | ✅ Done & pushed (`85733bd`) |
| 5 | Profile, i18n, branding, error states | ✅ Done locally (Flutter) |
| 6 | Deploy, app store, soft launch | ⏳ Next |

### Week 5 deliverables (2026-07-11)

- **i18n:** English, Somali (`so`), Arabic (`ar`) — `lib/l10n/app_localizations.dart`
- **Locale:** `localeProvider` persists language; profile change applies app-wide (RTL for Arabic)
- **Profile:** Name validation, API errors, language sync to API + locale
- **Branding:** `BrandWordmark`, branded splash loader, logo on home/login
- **Error states:** `ErrorState`, `LoadingState`, retry on orders/notifications/order detail

---

## 6. What's built

### API (`routes/api.php`)

- Auth: send-otp, verify-otp, logout
- User: profile, update, fcm-token, notifications
- Orders: list (filter), create, batch, detail, payment-sent
- Public settings

### Admin (`routes/web.php` + Livewire)

| Route | Component | Purpose |
|-------|-----------|---------|
| `/admin` | DashboardOverview | Pipeline stats |
| `/admin/incoming` | IncomingQueue | Submitted orders → quote |
| `/admin/orders/{id}/quote` | QuoteBuilder | Send quote |
| `/admin/payments` | PaymentConfirmationQueue | **Awaiting customer** + **Confirm payment** tabs |
| `/admin/tracking` | OrderTracking | Active / Delivered tabs |
| `/admin/settings` | SettingsPage | Fees, merchant number, SLAs |

### Flutter app (Week 3 scope)

- Phone + OTP login
- Home: paste link, single/batch submit
- My Orders: All / Active / Delivered filters
- Order detail: timeline, quote, 20s polling
- Payment: USSD, mark payment sent
- Notifications list
- Profile: name, address, language
- FCM stub (Firebase not configured — app runs without push)

---

## 7. Order flow (end-to-end)

```
Customer app          Admin dashboard              Customer app
─────────────         ─────────────────            ────────────
Paste link       →    Incoming Queue
Submit           →    (status: submitted)
                 ←    Quote Builder → send quote
(status: quoted) ←    Payments → Awaiting customer
Pay now          →
I've sent payment→    Payments → Confirm payment
(status: payment_pending)
                 ←    Confirm payment
                 ←    Tracking → Ordered → Shipped → Delivered
```

**Notifications:** In-app (`app_notifications` table) on every status change. FCM push only if `FCM_SERVER_KEY` + device token configured.

---

## 8. Product links (Shein / Amazon)

### Supported hosts

- Any host containing `shein.com` (includes `onelink.shein.com`, `m.shein.com`)
- Any `amazon.*` TLD

### Shein share paste

Shein app copies **marketing text + URL**, not just the link:

```
1 Pair Men's Shirt...
I discovered amazing products on SHEIN.com...
https://onelink.shein.com/42/5vdzzrjumi9v
```

**Fixed (2026-07-11):**

- **App:** `lib/utils/link_utils.dart` — extracts URL before validate/submit
- **API:** `app/Support/ProductLinkNormalizer.php` — same on `StoreOrderRequest` / `BatchOrderRequest`

---

## 9. Bugs fixed (2026-07-11 session)

| Issue | Cause | Fix |
|-------|-------|-----|
| `Route [login] not defined` on `/admin` | Auth middleware defaulted to `route('login')` | `bootstrap/app.php` → `redirectGuestsTo(route('admin.login'))` |
| Quoted orders invisible to admin | Gap between Incoming and Payment queues | Payments page: **Awaiting customer** / **Confirm payment** tabs |
| Big icon on Payments page | `.admin-alert` SVG had no size | `admin.css` — size all alert icons |
| Phone "Cannot reach server" | API on `127.0.0.1` only | Restart with `--host=0.0.0.0` |
| Order in admin but not in app | Laravel wraps JSON as `{data: {...}}`; app didn't unwrap | `ApiClient.unwrapResource()` + parsers updated |
| Shein full-share paste invalid | Whole blob sent as URL | URL extraction (app + API) |
| PHPUnit `could not find driver` | sqlite extensions disabled in php.ini | Enabled `pdo_sqlite` + `sqlite3` |

---

## 10. Uncommitted API changes (Week 4 + fixes)

**Modified:** `bootstrap/app.php`, `OrderService`, Livewire admin components, `admin.css`, `web.php`, `phpunit.xml`, `AppServiceProvider`, payment/incoming/tracking views, `StoreOrderRequest`, `BatchOrderRequest`, tests

**New:** `DashboardOverview`, `ProductLinkNormalizer`, E2E tests (`AuthApiTest`, `OrderFlowE2eTest`, `AdminLivewireTest`)

**Uncommitted App changes:**

- `lib/utils/link_utils.dart` (new)
- `lib/services/api_client.dart` — `unwrapResource`
- `lib/providers/orders_provider.dart`, `auth_provider.dart`
- `lib/screens/home/home_screen.dart`, `multi_order_screen.dart`

---

## 11. Known open items

| Item | Notes |
|------|-------|
| Commit & push Week 4 API work | User has not requested commit yet |
| Init git on Suuqsade-App | Not a repo yet |
| Firebase / FCM | Stub only — no `google-services.json` |
| Real merchant number | Placeholder `0000000` in settings |
| Real SMS OTP | Post-MVP |
| Hosting / deploy | TBD (Week 6) |
| LAN IP changes | Re-run `ipconfig` when switching Wi‑Fi |

---

## 12. Cursor / AI instructions

When working on Suuqsade:

1. **Manage both repos** — API changes often need matching Flutter updates (and vice versa).
2. **Read `CONTEXT.md`** for product rules; **read this file** for environment and session state.
3. **API:** `routes/api.php` for mobile; `routes/web.php` + `app/Livewire/Admin/` for admin.
4. **Validate links** via `ProductLink` rule + `ProductLinkNormalizer`.
5. **Prices in USD** everywhere.
6. **Physical device testing** requires `--host=0.0.0.0` and `--dart-define=API_BASE_URL=http://LAN_IP:8000`.
7. **Laravel API resources** wrap single objects in `{ "data": ... }` — Flutter must unwrap (see `ApiClient.unwrapResource`).
8. **Update this file** when completing a week, fixing major bugs, or changing run instructions.

---

## 13. Quick test checklist

- [ ] Phone browser opens `http://192.168.8.111:8000`
- [ ] App login with OTP `123456`
- [ ] Paste full Shein share text → order submits
- [ ] My Orders shows the order (same phone as login)
- [ ] Admin Incoming Queue shows order
- [ ] Admin sends quote → Payments → Awaiting customer
- [ ] App shows quote + Pay now
- [ ] App marks payment sent → Admin Confirm payment
- [ ] Admin advances tracking → App status updates

---

*Copy this file to `../Suuqsade-App/DEVELOPER_CONTEXT.md` when it changes (keep both in sync).*
