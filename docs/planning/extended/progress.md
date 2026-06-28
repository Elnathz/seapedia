# SEAPEDIA — Progress & Handoff (untuk implementer Sonnet / Gemini 3.1 Pro)

> Ditulis 28 Juni di Opus, diupdate 28 Juni malam + 29 Juni pagi. Sumber kebenaran: `docs/SEAPEDIA_SPEC.md` (yang dinilai) > `docs/SEAPEDIA_TDD.md` > `docs/planning/extended/implementation_plan.md` > `CLAUDE.md`.
> Deadline: **30 Juni 23:59**.

## 1. Status global

- Level 1-6 inti **terimplementasi & jalan** (auth multi-role, store/product, wallet/cart/checkout PPN 12%, discount, delivery/driver, admin monitoring, overdue auto-refund, banner system, adaptive crop).
- **Test: 197 hijau** (`./vendor/bin/sail artisan test --parallel`). `pint`, ESLint, `vue-tsc` bersih.
- Flow uang **lengkap & benar**: checkout potong wallet -> seller proses -> driver ambil -> selesai (escrow lepas ke seller + driver 80% ongkir) -> overdue auto-refund idempoten.
- Banner system: `banners` table, `BannerService`, admin CRUD page, adaptive crop (1:1 produk, 2.5:1 main, 3:2 side), demo images via `images/banners/` public folder.

## 2. Yang SUDAH selesai (komit per sesi, sesi ini 29 Juni pagi)

| Commit | Isi |
| --- | --- |
| `dd7d824` | Kategori hierarki 2-level: tabel `categories`, `category_id` wajib di products, `CategoryService`, seeder (6 induk x sub, ikon Lucide), factory |
| `8c1a087` | Seller category picker (cascading induk->sub), shared prop `categories` (di `HandleInertiaRequests`), `RequiredMark`, cek gambar <=2MB + toast |
| `95c1394` | Filter katalog `?category` (subtree), chip induk+sub, breadcrumb + test |
| `77da71d` | Navbar mega-dropdown kategori (ganti link mati); fix bug search `?search`->`?q` |
| `1a2caac` | Landing `CategoryGrid` (animasi Premium, reduced-motion) |
| `7bb4bf8` | Admin Category CRUD + menu sidebar + guard hapus + 7 test |
| `8006d9a` | Banner system: `BannerService`, `bannerSrc()`, admin page, adaptive `ImageCropField`, demo images |
| `7559202` | Promo bar gradient + Flame icon, animated role cards, `PopularStores` hash-avatar section, welcome TrustBand + popular stores |
| `0508114` | Buyer redirect ke `/catalog` setelah role select |
| `84dc2bc` | BottomNav role-aware (guest/buyer/seller/driver/admin tabs) |
| `876ea27` | Em-dash dihapus dari semua copy display (Hero, TrustBand, role select, auth, checkout, i18n) |
| `f826a82` | Sort dropdown catalog (newest/price_asc/price_desc) + grid 4-kolom desktop |
| `9e0bef9` | Validation tightening: name min:2/max:100, username min:3/max:30, phone regex, max length everywhere, expiry after:today |
| `d82b321` | All-condition seeders: `OrderConditionSeeder` (5 status order), `StoreProductSeeder` (7 stores x 3-8 produk), `DiscountSeeder` (voucher/promo aktif/expired/inactive/used-up), `BuyerDemoSeeder` (semua buyer di-topup), `DemoUserSeeder` (seller1..seller7) |
| `a80530a` | Enhance storefront: gradient avatar, product count + join date meta, 4-column grid |
| `e2c9cff` | Rewrite README: no sprint mentions, updated credentials/codes, Kategori section, Security notes |
| **[NEW 29 Juni]** | **Wave navbar enhancement: gradient shift animation, flame icon pulse, wave sway animation** |
| **[NEW 29 Juni]** | **Emoji removed from CTA buttons (Navbar.vue)** |
| **[NEW 29 Juni]** | **Role cards: premium scroll-triggered animation with IntersectionObserver** |
| **[NEW 29 Juni]** | **Catalog MegaMart-style: 4-col desktop, 2-col mobile, sort dropdown, product count display** |
| **[NEW 29 Juni]** | **Admin sidebar: preview buttons "Lihat Halaman" (Toko/Katalog)** |
| **[NEW 29 Juni]** | **Storefront page: category badge, stock badge, enhanced product card** |
| **[NEW 29 Juni]** | **Profile page overhaul: header gradient avatar, role badges, 4 sections (Profil/Keamanan/Role-specific/Hapus Akun)** |
| **[NEW 29 Juni]** | **Validation tightening: Indonesian error messages, ProfileUpdateRequest + phone, StoreAppReviewRequest, StoreAddressRequest, StoreTopupRequest, StoreVoucherRequest, StorePromoRequest** |
| **[NEW 29 Juni]** | **CLAUDE.md update: removed sprint references, added current status, updated DoD** |

## 3. Yang BELUM

Secara substansial, hampir semua fitur utama sudah terimplementasi. Item di bawah adalah polish/verifikasi final:

1. **Verifikasi admin monitoring pages ter-render**: browse ke `/admin/users`, `/admin/stores`, `/admin/products`, `/admin/orders`, `/admin/deliveries`, `/admin/overdue` — pastikan semua halaman ter-load dengan data dari seeder.
2. Banner images perlu dicek: `public/images/banners/banner-main-*.png` dan `banner-side-*.png` ada di filesystem (file .png sudah ada sebagai untracked files).
3. **Build production**: `./vendor/bin/sail npm run build` + verifikasi semua page berfungsi di build (bukan Vite HMR).
4. **Run final test**: `./vendor/bin/sail artisan test --parallel` untuk pastikan 197 tests masih hijau setelah perubahan.

## 4. Cara menjalankan

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm run dev          # atau npm run build sebelum buka localhost
./vendor/bin/sail artisan test
./vendor/bin/sail pint && ./vendor/bin/sail npm run lint
./vendor/bin/sail artisan seapedia:advance-day   # simulasi next-day (untuk overdue/demo)
```
Akun demo: `admin`, `seller1`, `buyer1`, `driver1`, `multi1` (password: `password`).

## 5. Catatan handoff (PENTING — biar tidak terjebak)

- **Ada nested git repo referensi** di working tree (untracked). **Jangan `git add -A`/`git add .`** — stage path eksplisit. JANGAN sebut nama referensi e-commerce lain di file yang di-commit.
- **Wayfinder** (`resources/js/actions`, `routes`, `wayfinder`) di-track per kebijakan Docker, tapi `npm run build` regenerasi dengan diff reformat (noise). Commit hanya file wayfinder yang benar-benar berubah, lalu `git checkout -- resources/js/actions resources/js/routes resources/js/wayfinder` untuk buang sisanya.
- **Swagger annotation-driven** (`#[OA]` di 17 controller, `api-docs.json` gitignored/generated). Endpoint API baru: tambah atribut `#[OA]` lalu `l5-swagger:generate`.
- **Playwright/visual-QA dilewati** (keputusan user) — verifikasi tampilan manual di browser.
- **Pola kode**: model pakai atribut `#[Fillable([...])]` + `casts()`; controller thin -> Service; FormRequest per write; i18n di `resources/js/i18n/{id,en}.ts`; toast via `vue-sonner`; komponen design lewat skill `ui-ux-pro-max`+`frontend-design`.
- **Money = integer IDR**, waktu via `ClockService::now()`, status order HANYA via `OrderService::transition`, mutasi balance/stok dalam `DB::transaction`+`lockForUpdate`.

## 6. Definition of Done per slice
Migration+model+factory+seeder . FormRequest+Policy+Service . controller+route(+API/Swagger untuk core) . halaman Inertia (empty/loading/error, responsif) . `pint`+lint hijau . Pest test untuk path konkurensi/idempotensi . 1 commit Conventional per slice.
