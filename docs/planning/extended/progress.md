# SEAPEDIA — Progress & Handoff (untuk implementer Sonnet / Gemini 3.1 Pro)

> Ditulis 28 Juni di Opus. Sumber kebenaran: `docs/SEAPEDIA_SPEC.md` (yang dinilai) > `docs/SEAPEDIA_TDD.md` > `docs/planning/extended/implementation_plan.md` > `CLAUDE.md`.
> Deadline: **30 Juni 23:59**.

## 1. Status global

- Level 1–6 inti **terimplementasi & jalan** (auth multi-role, store/product, wallet/cart/checkout PPN 12%, discount, delivery/driver, admin monitoring dasar, overdue auto-refund).
- **Test: 189 hijau** (`./vendor/bin/sail artisan test`). `pint`, ESLint, `vue-tsc` bersih.
- Flow uang **lengkap & benar**: checkout potong wallet → seller proses → driver ambil → selesai (escrow lepas ke seller + driver 80% ongkir) → overdue auto-refund idempoten. Lihat audit di `implementation_plan.md` §4.6.

## 2. Yang SUDAH selesai sesi ini (fitur Kategori + perbaikan)

| Commit | Isi |
| --- | --- |
| `dd7d824` | Kategori hierarki 2-level: tabel `categories`, `category_id` wajib di products, `CategoryService`, seeder (6 induk × sub, ikon Lucide), factory |
| `8c1a087` | Seller category picker (cascading induk→sub), shared prop `categories` (di `HandleInertiaRequests`), `RequiredMark`, cek gambar ≤2MB + toast |
| `95c1394` | Filter katalog `?category` (subtree), chip induk+sub, breadcrumb + test |
| `77da71d` | Navbar mega-dropdown kategori (ganti link mati); fix bug search `?search`→`?q` |
| `1a2caac` | Landing `CategoryGrid` (animasi Premium, reduced-motion) |
| `7bb4bf8` | Admin Category CRUD + menu sidebar + guard hapus + 7 test |
| `8006d9a` | Seller product list: tabel → card grid responsif + badge kategori |
| `771a089` · `8026e32` | chore: eslint exclude megamart · track skill motion-design/decision-guard |

Validasi produk sudah diketatkan (name max:150, price min:100|max:100jt, stock max:1jt, category required).

## 3. Yang BELUM (urut prioritas; detail di implementation_plan.md)

1. **§4.2 Seeder semua kondisi** — demo-ready lintas role (paling penting untuk penilaian demo).
2. **§3.3 Validation pass** — tighten FormRequest non-produk + RequiredMark/InputError/toast menyeluruh (graded security).
3. **§2.1 Admin monitoring** (users/stores/products/orders/deliveries/overdue) — graded 3pt.
4. **§4.3 Responsive <400px + buang em-dash** — navbar mepet (screenshot 378px), copy natural.
5. **§4.0 Banner admin (layout megamart)** + **§4.1 crop adaptif** (produk 1:1, banner 2.5:1/3:2) — `vue-advanced-cropper`.
6. **§1.4 buyer redirect ke /catalog** + **§1.5 bottom-nav role-aware** (UX mobile).
7. **§4.5 enhance halaman seller** (sudah ada versi dasar).
8. **§4.4 aset gambar** (generate via prompt Gemini di plan) → pasang ke seeder banner/produk.
9. **§3.1 README rewrite** (buang kata "sprint", tambah kategori + security notes) + **§3.2 CLAUDE.md**.
10. Sisa landing: §1.1 wave/promo, §1.2 RoleCards premium, §1.3 popular stores.

## 4. Cara menjalankan

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm run dev          # atau npm run build sebelum buka localhost
./vendor/bin/sail artisan test
./vendor/bin/sail pint && ./vendor/bin/sail npm run lint
./vendor/bin/sail artisan seapedia:advance-day   # simulasi next-day (untuk overdue/demo)
```
Akun demo: `admin`, `seller1`, `buyer1`, `driver1`, `multi1` (password lihat README / DemoUserSeeder).

## 5. Catatan handoff (PENTING — biar tidak terjebak)

- **megamart/e-commerce/ = nested git repo**, muncul sebagai `?? megamart/`, BUKAN gitignored. **Jangan `git add -A`/`git add .`** — stage path eksplisit. Pakai megamart hanya sebagai **referensi internal**; JANGAN sebut megamart di file yang di-commit (CLAUDE.md aturan: tanpa jejak referensi e-commerce lain).
- **Wayfinder** (`resources/js/actions`, `routes`, `wayfinder`) di-track per kebijakan Docker, tapi `npm run build` regenerasi dengan diff reformat (noise). Commit hanya file wayfinder yang benar-benar berubah (controller/route baru, `git add -f` untuk file baru), lalu `git checkout -- resources/js/actions resources/js/routes resources/js/wayfinder` untuk buang sisanya. Ada perubahan `.gitignore` uncommitted milik user (ingin ignore wayfinder) — JANGAN buang; kebijakan ini belum final.
- **Swagger annotation-driven** (`#[OA]` di 17 controller, `api-docs.json` gitignored/generated). Endpoint API baru: tambah atribut `#[OA]` lalu `l5-swagger:generate`. Jangan edit JSON manual.
- **Playwright/visual-QA dilewati** (keputusan user) — verifikasi tampilan manual di browser; catat di sini bila ada gap.
- **Pola kode**: model pakai atribut `#[Fillable([...])]` + `casts()`; controller thin → Service; FormRequest per write; i18n di `resources/js/i18n/{id,en}.ts` (BUKAN lang JSON untuk UI); toast via `Inertia::flash('toast', ...)` → `flashToast.ts` → vue-sonner; komponen design lewat skill `ui-ux-pro-max`+`frontend-design`, animasi lewat `motion-design`.
- **Money = integer IDR**, waktu via `ClockService::now()`, status order HANYA via `OrderService::transition`, mutasi balance/stok dalam `DB::transaction`+`lockForUpdate`.

## 6. Definition of Done per slice
Migration+model+factory+seeder · FormRequest+Policy+Service · controller+route(+API/Swagger untuk core) · halaman Inertia (empty/loading/error, responsif) · `pint`+lint hijau · Pest test untuk path konkurensi/idempotensi · 1 commit Conventional per slice (lihat `CLAUDE.md` §commit).
