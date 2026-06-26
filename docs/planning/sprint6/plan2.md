# Sprint 6 Plan — Addendum v2: White-body re-theme, light-only, new "Bright Sea" brand + logo

> **Addendum to `plan.md`, same sprint (final).** This document refines the UI direction agreed in a
> later brainstorming session and **supersedes** the parts of `plan.md` that said *"anchor on existing
> teal tokens — no new palette"* (§ Design direction) and the implicit *"keep dark mode"* assumption.
> Everything else in `plan.md` (T1–T12 scope, security, iPaymu drop, docs, deploy) still stands.

## Why this addendum exists

The original plan re-themed every page on the **existing uniform deep-teal** tokens with dark mode
kept. The owner has since decided on a **marketplace-style identity** (Tokopedia / Shopee /
JakartaNotebook): a **white body** with the brand color used only as an **accent** (logo, banner, CTA,
active states), plus a **new, brighter "sea" hue**, a **new SVG logo**, and **no dark mode**. UI is
refined **one page at a time**, not in a single sweep.

## Locked decisions (this session)

1. **Light-only.** Dark mode is dropped for the demo (decision-guard: costly-but-reversible, no money/
   state at risk; brand references are all light-only; final sprint; Playwright QA already skipped).
   **Implementation:** hide the appearance toggle and force `light` — **do NOT delete** the `.dark`
   token blocks or `useAppearance` infra (kept so it can be re-exposed later). See "Dark mode removal".
2. **White body + accent brand.** Page/card backgrounds are white / `slate-50`. The sea color appears
   only on logo, banner/hero, primary buttons, links, active/selected states, and key badges.
3. **New "Bright Sea" palette** (Parangtritis-sea turquoise), two-tier for contrast:
   | Role | Hex | HSL | Usage |
   |---|---|---|---|
   | Sea (brand, bright) | `#13B5C4` | `185 82% 42%` | Logo, banner, hero, large fills |
   | Sea-deep (interactive) | `#0A7180` | `188 85% 27%` | `--primary`: buttons, links, teal text on white (AA) |
   | Ink (heading) | `#062E36` | `190 80% 12%` | Headings, dark wordmark |
   | Coral (accent pop) | `#FF6B5A` | `7 100% 68%` | Sale/discount tags, badges, alerts |
   | Body | `#FFFFFF` / `#F8FAFC` | — | Page & card backgrounds |
   | Muted text | `#475569` | — | Secondary text |
   - `--primary` = **Sea-deep** (white text on it stays AA-legible). **Sea (bright)** is exposed as a
     separate `--brand` token + utility for logo/banner/hero only.
   - Hero gradient: `#13B5C4 → #0A7180` (shallow-to-deep sea), top-left to bottom-right.
4. **New logo.** Generated as SVG via Gemini 3.1 Pro (mark = abstract "S" from two wave crests that
   also reads as a book page; wordmark "SEAPEDIA" outlined to `<path>`, two-tone Ink/Sea-deep). The
   prompt lives in the brainstorming thread / `progress2.md`. Deliverables: horizontal lockup SVG +
   icon-only SVG (favicon/app icon). Place under `resources/js/components/brand/` (Vue wrapper) and/or
   `public/` for the favicon.
5. **Fonts.** Wordmark: **Plus Jakarta Sans ExtraBold** (outlined in the SVG, so no runtime font dep).
   UI body: keep **Instrument Sans**.
6. **One page at a time.** Refine and verify each page/area before moving to the next — do not batch.

## Dark mode removal (how)

- Force light at the root: ensure the app boots without the `dark` class and ignores the stored/OS
  preference (set appearance to `light` and stop applying `dark`).
- Remove the toggle from the user-facing UI: `settings/Appearance.vue` + `AppearanceTabs.vue` entry
  point (hide the control / route), so no one can switch to a half-themed dark.
- **Keep** the `.dark` blocks in `app.css` and the `useAppearance` composable in the codebase (dead
  but harmless) so re-enabling later is a small change, not a rebuild.
- Acceptance: no route renders dark; toggling OS dark does nothing; no console error from missing
  appearance state.

## Token & palette migration (do this FIRST, before page refinement)

1. **Rewrite the `:root` tokens** in `resources/css/app.css` to the Bright Sea palette: `--background`
   white/`slate-50`, `--foreground` Ink, `--primary` Sea-deep, add `--brand` = Sea (bright),
   `--accent`/destructive-adjacent Coral for sale tags. Keep the shadcn token names intact.
2. **Sweep hardcoded colors → semantic tokens.** Replace literal `bg-teal-*` / `text-teal-*` /
   `text-emerald-*` / ad-hoc `bg-white` with `bg-background` / `text-primary` / `bg-card` etc. so the
   palette lives in one place. (Audit showed only a handful of files: status helpers in
   `resources/js/lib/*Status.ts`, a few role/driver/seller pages, `landing/RoleCards.vue`.)
3. Add favicon (icon-only SVG) + wire the new logo lockup into the header/landing.

## Aset visual — hero & foto produk

### Logo (DONE — Opus, asset-only)
- `public/favicon.svg` = mark "Tiga Arus" (three-current whirlpool, asimetris sesuai pilihan owner;
  arms rotate 55/190/315, gradasi laut `#13B5C4 / #0E9AA6 / #0A7180`, pusaran + dot di tengah).
- `public/seapedia-logo.svg` = lockup (mark + wordmark "SEA"`#0A7180` / "PEDIA"`#13B5C4`, Plus Jakarta
  Sans 800). Favicon sudah ter-link di `resources/views/app.blade.php`.
- **R0 sisa (Sonnet):** bungkus lockup jadi `resources/js/components/brand/Logo.vue` (`variant="full|mark"`)
  dan pakai di header + landing. Favicon kecil 16–32px: lengan menyebar — kalau perlu, Sonnet boleh
  pakai varian mark yang sedikit lebih rapat khusus favicon (arsip eksperimen ada di scratchpad).

### Hero image (landing R1)
Gaya: ilustrasi flat/semi-flat bertema laut, sewarna brand, **background putih**, banyak ruang kosong
untuk headline. Hindari foto stok generik. **Generate via AI image-gen, lalu export PNG transparan.**

**Prompt (objek = tiga peran, gema logo):**
```text
A clean, modern marketing hero ILLUSTRATION for an Indonesian online marketplace
called SEAPEDIA. Flat / semi-flat vector style with soft gradients, friendly and
youthful (student vibe). Ocean / sea theme. Strict palette: bright sea #13B5C4,
deep teal #0A7180, ink #062E36, warm accent #FF6B5A, on a WHITE background.
Show THREE friendly young Indonesian characters representing one app's three roles:
(1) a BUYER holding a phone and a shopping bag, (2) a SELLER beside a small stall
with boxes/products, (3) a DELIVERY DRIVER on a scooter. Connect them with flowing
turquoise wave / current lines that swirl like a three-armed whirlpool (echoing the
logo). Lots of negative space on the LEFT for headline text. Airy, light, premium.
16:9 composition, high resolution, NO text and NO letters in the image, transparent
or pure-white background.
```
**Alternatif (lebih murah/aman):** background abstrak gelombang (tanpa karakter) —
ganti kalimat karakter dengan: *"an abstract composition of layered turquoise wave/
current shapes and a soft whirlpool motif, with a few floating product cards"*.

**Dimensi hero:**
- Desktop (≥768px): **2400×1350** (16:9), tampil full-width di hero; teks di-overlay via kode
  (jangan teks di gambar). Simpan `< 400KB`, WebP + fallback.
- Mobile (<768px): jangan pakai gambar 16:9 yang sama (kepotong/kekecilan). Siapkan crop/varian
  **1080×1080** (1:1) atau **1080×1350** (4:5), fokus 1 karakter / motif pusaran saja.

### Perilaku hero di MOBILE (jawaban: hero TIDAK hilang, tapi diringkas)
- **Desktop:** hero penuh = headline + subhead + search/CTA di kiri, ilustrasi di kanan.
- **Mobile:** hero **tetap ada** tapi **versi ringkas** — tinggi lebih pendek, layout 1 kolom
  (headline + subhead + CTA + search bar ditumpuk), ilustrasi **mengecil di bawah / jadi banner pendek
  atau dijadikan background dengan overlay**. Tujuan: jangan dorong grid produk terlalu jauh ke bawah.
- Pola alternatif marketplace (boleh dipertimbangkan Sonnet): di mobile, ganti hero besar dengan
  **search-first header + chip kategori + 1 promo banner**, lalu langsung grid produk. Pilih salah satu
  dan terapkan konsisten; default = **hero ringkas** di atas.

### Foto produk (catalog + detail) — ukuran px
Rasio **1:1 (persegi)** untuk semua foto produk biar grid konsisten (pola Tokopedia/Shopee).
| Pemakaian | Simpan (source) | Tampil (≈) | Catatan |
|---|---|---|---|
| Kartu katalog (grid) | **800×800** | 240–320px | crop center, 1 foto utama/produk |
| Halaman detail (galeri utama) | **1200×1200** | 480–600px | foto utama + 2–4 thumbnail 1:1 |
| Thumbnail galeri detail | **300×300** | 64–80px | turunan dari foto galeri |
| Banner toko (opsional) | **1600×500** (16:5) | full-width | hanya kalau halaman store butuh |

- Format **WebP** (fallback JPG), target `< 200KB`/foto katalog, `< 350KB`/foto detail.
- Sumber demo: foto asli per kategori (boleh Unsplash/AI-gen), **konsisten 1:1**, latar bersih.
- Simpan di `public/products/<slug>/main.webp` + `1.webp..n.webp`; seeder menunjuk path ini.
- Placeholder wajib: kalau foto tak ada, render kotak 1:1 dengan logo-mark mono (empty state).

### Navbar (guest) & hero interaktif (R1)
- **Hapus toggle bahasa dari navbar.** Pindahkan pemilih bahasa (ID/EN) ke halaman **Settings user**
  (di samping Appearance); guest default **ID**. Konsisten dgn memory i18n-deferred — toggle tetap ada,
  tapi bukan di header. Pindahkan `resources/js/components/LanguageTabs.vue` dari header ke `settings/`.
- **Isi navbar biar tidak sepi (urutan kiri → kanan):**
  1. Logo (mark + wordmark, dari `Logo.vue`).
  2. **Search bar besar** — elemen utama marketplace, **prioritas #1** + dropdown **"Kategori"**.
  3. Link ringkas: **"Cara Kerja"**, **"Jadi Mitra"** (seller/driver).
  4. Kanan: **"Masuk"** (ghost) + **"Daftar"** (solid sea).
  - Opsional: **top-bar promo tipis** di atas navbar — *"Satu akun untuk Belanja · Jualan · Antar 🌊"*.
  - Saat login: search tetap; ganti Masuk/Daftar dgn **ikon keranjang + avatar/role-switcher**.
- **Hero interaktif + animasi:**
  - **Motif pusaran logo** sebagai elemen hero yang **berputar pelan** (CSS `@keyframes`, ~30–40s/putaran) — on-brand, ringan.
  - **Gelombang SVG beranimasi** sebagai wave-divider di bawah hero + gradient halus bergerak.
  - **Entrance animation** headline/subhead/CTA (fade-up bertahap saat load).
  - **Parallax/tilt halus** pada ilustrasi: **desktop** mengikuti mouse, **mobile** mengikuti
    **scroll** (Opsi B decision-guard — bukan gyro). Throttle pakai `requestAnimationFrame`, amplitudo
    kecil (~8–12px). Gyro/deviceorientation opsional & off-by-default (hindari prompt izin iOS + jank).
  - Opsional: **headline rotator** kata peran — *"Belanja. / Jualan. / Antar."*
  - **Wajib hormati `prefers-reduced-motion`** → matikan animasi non-esensial.
  - **Tooling:** CSS-first (tanpa dependency baru). Kalau perlu orchestration, boleh `@vueuse/motion`
    (ringan) — catat sebagai dependency baru di `progress2.md` + alasannya; jangan tarik GSAP kecuali benar-benar perlu.
  - Mobile: **parallax NYALA via scroll** (amplitudo kecil, rAF-throttled) + pusaran + fade-up;
    matikan otomatis kalau `prefers-reduced-motion` aktif atau perangkat low-end.

## Page refinement order (one at a time — mirrors the Final-Demo-Checklist)

Each is its own slice + commit; finish and eyeball before starting the next:

- **R0 · Tokens + logo + dark-mode removal** (the migration above) — `feat(ui): adopt bright-sea
  light-only palette and new logo`.
- **R1 · Landing + public reviews** — white body, sea hero/banner, logo lockup, coral sale accents.
- **R2 · Auth (login / register / role-select)** — white cards on subtle sea-tinted backdrop.
- **R3 · Catalog + product detail + store** — white product cards, sea CTAs, coral discount badges.
- **R4 · Seller pages** (dashboard / store / product / incoming).
- **R5 · Buyer pages** (cart / checkout / orders / wallet).
- **R6 · Driver + Admin** (consistency pass).

> These R-slices **replace** the look-and-feel goals of `plan.md` T3–T8 (same pages, new direction).
> T1 (security), T2 (iPaymu), T9–T12 (docs/deploy/close-out) are unaffected.

## Design + Definition of Done (per slice)

- Designed via `ui-ux-pro-max` + `frontend-design` (golden rule 11a) — deliberate hierarchy/signature,
  not raw shadcn defaults. Each page needs a real signature element, not a grid of identical cards.
- White body, sea used as accent only; coral reserved for sale/alert; AA contrast on all text/buttons.
- shadcn-vue components installed before use (golden rule 12); semantic tokens only, no new hardcoded
  hexes in components.
- Empty / loading / error states present; responsive; **target demo display 1920×1080 desktop** (plus
  sane 360/768/1280 behavior).
- `pint`, `npm run lint`, `npm run build`, `vue-tsc`, Pest all pass. One focused commit per slice.

## Instructions for the Sonnet implementer

- Switch `/model` → **Sonnet** before any feature code.
- Do **R0 first** (tokens + logo + dark-mode removal); it unblocks every other slice. Then R1→R6 **in
  order, one at a time** — do not batch pages.
- Design every slice through `ui-ux-pro-max` + `frontend-design`. shadcn-vue is the substrate only.
- **No Playwright** (memory) — note the visual-QA gap in `progress2.md`.
- **No `.env` edits** (memory) — hand the owner any key/value to set.
- Commit discipline: per-slice `feat(ui)/fix(ui)` commits; **do not** commit `progress2.md` per task —
  update it locally and fold it into the sprint's closing commit (same rule as `plan.md`).
- Before each commit: `/code-review` + project `code-review` skill, `simplify`, then `verify`; run
  pint/lint/build/vue-tsc/Pest — all green.
- The new logo SVG is generated by the **owner** in Gemini 3.1 Pro (prompt in `progress2.md`); the
  implementer wires the provided SVG into `resources/js/components/brand/` + favicon.

## Perlu dikerjakan next sprint (carry-over)

**None — Sprint 6 is the final sprint.** Any R-slice not completed is a release-blocker to resolve
this sprint; if something genuinely cannot ship, record it under "Known gaps at submission" in
`progress2.md` with the reason.
