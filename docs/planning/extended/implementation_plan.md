# SEAPEDIA — Prioritized Implementation Plan (Deadline: 30 Juni 23:59)

> **~46 jam tersisa.** Diurutkan berdasarkan impact scoring tertinggi ke terendah.
> Setiap section punya instruksi detail untuk Claude Code Sonnet.

---

## Confirmed Decisions

| #              | Keputusan                                                                                                                                                                               |
| -------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Navbar wave    | Static SVG wave di bawah navbar — warna teal sesuai palette (`--primary: hsl(188 85% 27%)`, `--brand: hsl(185 82% 42%)`). Tanpa animasi.                                           |
| Buyer redirect | Buyer setelah role select langsung ke`/catalog`, bukan dashboard. Dashboard tetap accessible via sidebar/bottom nav.                                                                  |
| README/Docker  | Dua-duanya — utamakan plain`php artisan serve`, Docker sebagai alternatif. README detail dan menarik.                                                                                |
| Icons          | **Gunakan Lucide icons saja, JANGAN emoji.** Semua icon di UI harus dari `@lucide/vue`.                                                                                         |
| Referensi      | **JANGAN sebut referensi e-commerce lain di file manapun yang di-commit** (CLAUDE.md, planning, README, komentar kode). Internal knowledge saja — tidak boleh ada jejak di repo. |
| Kategori       | **Hierarki 2 level** (Induk → Sub). **Admin** yang CRUD kategori; **Seller hanya memilih**. Produk **wajib** punya kategori (`category_id NOT NULL`), nempel ke **sub**; filter induk = produk induk + semua sub-nya. Muncul di 4 surface: landing grid, filter katalog, dropdown navbar, halaman kategori. Spec tidak mewajibkan kategori — ini enhancement bonus, **jangan korbankan requirement Level 1–7**. |
| Validasi form  | **Tighten** semua FormRequest yang sudah ada (bukan bikin dari nol — 25 request sudah ada): max length wajar, range numerik, image ≤ 2MB. Server-side = batas keamanan (Level 7A graded); cermin client-side untuk UX (`maxlength`, cek ukuran file sebelum upload). |
| Tanda wajib    | Semua field wajib pakai komponen `RequiredMark` (`<span class="text-destructive">*</span>` + `aria-label="wajib"`). Dipasang di label semua form: register, store, produk, kategori, alamat, top-up, checkout, review, voucher/promo. |
| Feedback error | `InputError` per-field di **semua** form + toast `vue-sonner` (sudah terinstall) untuk flash/error global (login salah, upload kebesaran). Tombol submit `disabled` + spinner saat `form.processing`. |
| Swagger        | `storage/api-docs/api-docs.json` **statis/manual** (0 anotasi `@OA` di kode, 33 path terdokumentasi). **JANGAN jalankan `l5-swagger:generate`** (akan meng-wipe doc). Tambah path endpoint kategori baru secara **manual** ke JSON. |

---

## Color Palette Reference

Dari [app.css](file:///wsl.localhost/Ubuntu/home/farros/seapedia/resources/css/app.css):

```
Light Mode:
  --primary:          hsl(188 85% 27%)   → Deep teal
  --brand:            hsl(185 82% 42%)   → Bright teal
  --secondary:        hsl(185 50% 94%)   → Very light teal
  --accent:           hsl(185 60% 93%)   → Light teal
  --background:       hsl(0 0% 100%)     → White
  --foreground:       hsl(190 80% 12%)   → Very dark teal/navy
  --muted:            hsl(210 40% 98%)   → Off-white
  --muted-foreground: hsl(215 16% 47%)   → Gray
  --border:           hsl(214 32% 91%)   → Light gray
  --destructive:      hsl(0 72.2% 50.6%) → Red

Hero gradient: from-[#13B5C4] to-[#0A7180]
```

---

## TIER 1: Critical — Kerjakan Malam Ini (28 Juni)

---

### 1.0 Category System (Hierarki Induk → Sub) — FOUNDATION, KERJAKAN PALING AWAL

> [!IMPORTANT]
> Kategori mengubah skema `products` + factory + seeder. **WAJIB selesai sebelum** task produk/katalog/admin lain — kalau tidak `migrate:fresh --seed` gagal (FK `category_id`).
> Decisions terkunci: hierarki **2 level**; **admin** CRUD, seller hanya pilih; `category_id NOT NULL` nempel ke **sub**; filter induk = subtree (induk + sub).

#### 1.0a Backend — schema, model, service, seed

```
FILE: database/migrations/2026_06_24_020000_create_products_table.php
  Tambah SETELAH baris store_id (migrate:fresh dipakai → edit migration langsung):
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
  Tambah juga: $table->index('category_id');

NEW FILE: database/migrations/2026_06_24_015000_create_categories_table.php
  (timestamp LEBIH AWAL dari products agar FK valid saat migrate)
  Schema::create('categories', function (Blueprint $table) {
      $table->id();
      $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
      $table->string('name');
      $table->string('slug')->unique();
      $table->string('icon')->nullable();        // nama lucide, mis. "UtensilsCrossed"
      $table->boolean('is_active')->default(true);
      $table->unsignedInteger('sort_order')->default(0);
      $table->timestamps();
      $table->index('parent_id');
  });

NEW FILE: app/Models/Category.php
  - $fillable = ['parent_id','name','slug','icon','is_active','sort_order']
  - casts: is_active => boolean
  - getRouteKeyName(): return 'slug'
  - parent()   : belongsTo(Category, 'parent_id')
  - children() : hasMany(Category, 'parent_id')->orderBy('sort_order')
  - products() : hasMany(Product)
  - scopeActive($q)  => $q->where('is_active', true)
  - scopeRoots($q)   => $q->whereNull('parent_id')

FILE: app/Models/Product.php
  - tambah 'category_id' ke $fillable
  - category(): belongsTo(Category)

NEW FILE: app/Services/CategoryService.php  (framework-agnostic, golden rule 2)
  - tree(): Category::active()->roots()->with(['children' => active, withCount('products')])
            ->withCount('products')->orderBy('sort_order')->get()   // untuk navbar + landing
  - descendantIds(Category $c): array  → [$c->id, ...$c->children->pluck('id')]  (2 level cukup)
  - listForAdmin(): roots()->with('children')->withCount('products')->get()

NEW FILE: database/seeders/CategorySeeder.php  — ~6 induk @ 2-3 sub, icon lucide:
  Makanan (UtensilsCrossed)        → Makanan Berat, Cemilan
  Minuman (CupSoda)                → Kopi, Teh, Jus
  Elektronik (Smartphone)          → Aksesori HP, Audio
  Fashion (Shirt)                  → Pria, Wanita
  Kebutuhan Harian (ShoppingBasket)→ Sembako, Perawatan
  Lainnya (Package)                → Umum
  Daftarkan di DatabaseSeeder SEBELUM StoreProductSeeder.

FILE: database/factories/ProductFactory.php + StoreProductSeeder.php + BuyerDemoSeeder.php
  - assign category_id = id salah satu SUB-kategori acak (Category::whereNotNull('parent_id')->inRandomOrder()->first()).
```

#### 1.0b Admin Category CRUD (menu admin baru — nilai Level 6A)

```
NEW: app/Http/Controllers/Web/Admin/AdminCategoryController.php
  - index()  : Inertia render 'admin/categories/Index' + CategoryService::listForAdmin()
  - store()/update()/destroy() : thin → CategoryService method (Str::slug(name) untuk slug)
  - destroy(): TOLAK jika kategori punya produk → redirect()->back() dengan flash error.

NEW FormRequests: StoreCategoryRequest, UpdateCategoryRequest
  name      => ['required','string','min:2','max:80']
  parent_id => ['nullable','integer','exists:categories,id']
  icon      => ['required','string','max:50']

ROUTE (grup admin, web.php):
  Route::resource('admin/categories', AdminCategoryController::class)
       ->only(['index','store','update','destroy'])->names('admin.categories');

PAGE: resources/js/pages/admin/categories/Index.vue
  - Tabel tree: baris INDUK, lalu baris SUB ter-indent (pl-8) di bawahnya.
  - Tombol "Tambah Kategori" → Dialog (shadcn) form: name*, parent (Select: "—Induk—" atau pilih induk), icon (Select dari daftar lucide kurasi).
  - Aksi edit (Dialog prefilled) + hapus (konfirmasi). RequiredMark + InputError dipakai.

SIDEBAR: resources/js/components/AppSidebar.vue → tambah item admin "Kategori" (icon Tags).
```

#### 1.0c Seller — category picker + validasi (FUNGSIONAL; polish visual di §2.4)

```
FILE: resources/js/pages/seller/products/Form.vue
  - Cascading select: Select INDUK → Select SUB (sub di-filter dari induk terpilih, sumber data = shared prop `categories`).
  - Submit category_id = id SUB terpilih (atau induk bila induk tak punya sub).
  - Field wajib pakai RequiredMark.

FILE: app/Http/Requests/StoreProductRequest.php + UpdateProductRequest.php
  - TAMBAH: 'category_id' => ['required','integer','exists:categories,id']
  - TIGHTEN (lihat matriks §3.3): name max:150, price min:100 max:100000000, stock max:1000000.

FILE: app/Services/CatalogService.php + query Product mana pun yang me-render list
  - eager load: ->with('category') untuk hindari N+1 (golden rule 16).
```

#### 1.0d Empat surface UI kategori

```
SHARED PROP — FILE: app/Http/Middleware/HandleInertiaRequests.php
  Tambah ke array share(): 'categories' => fn () => app(CategoryService::class)->tree()
  (di-resolve lazy via closure; satu query, dipakai navbar + landing tanpa query per-controller)

1) LANDING GRID — NEW: resources/js/components/landing/CategoryGrid.vue
   - Grid induk: ikon lucide (dynamic <component :is>) dalam lingkaran gradien + jumlah produk.
   - Klik kartu → /catalog?category={slug}.
   - Animasi stagger fade-up (IKUTI motion-design skill: IntersectionObserver, ~400-500ms,
     cubic-bezier(0.4,0,0.2,1), hormati prefers-reduced-motion).
   - Sisipkan di Welcome.vue setelah PopularStores (urutan: Hero → TrustBand → PopularStores
     → CategoryGrid → FeaturedStrip → RoleCards → Reviews).

2) FILTER KATALOG — FILE: resources/js/pages/catalog/Index.vue + CatalogController + CatalogService
   - CatalogController::index terima ?category=slug → CatalogService filter
     where category_id IN descendantIds(kategori).  Gabung dgn ?q (search) & ?sort (§2.2).
   - UI: chip INDUK (horizontal-scroll di mobile, sidebar di desktop) + tombol "Semua".
     Pilih induk → tampilkan SUB-chip. Update URL via Inertia router (preserveState, preserveScroll).

3) NAVBAR DROPDOWN — FILE: resources/js/components/Navbar.vue
   - Link "Katalog" jadi `navigation-menu` (shadcn, sudah terinstall) → dropdown daftar
     INDUK + SUB (dari shared prop `categories`).
   - Mobile: kategori masuk ke dalam Sheet menu (jangan dropdown hover di mobile).

4) HALAMAN KATEGORI — FILE: catalog/Index.vue + CatalogController
   - Saat ?category aktif, CatalogController kirim prop `activeCategory`.
   - Tampilkan header: nama kategori + deskripsi singkat + breadcrumb (Beranda › Induk › Sub).
```

> **Visual QA:** Playwright dilewati (sesuai keputusan) — catat di `progress.md`.
> **DoD kategori:** migration+model+factory+seeder · FormRequest+Policy(admin)+Service · controller web+route admin · `/api/v1/categories` (opsional, tambah path ke api-docs.json manual) · pages ber-empty/loading/error state · `pint` + lint hijau · commit terpisah (`feat(catalog): add product category hierarchy`).

---

### 1.1 Wave Navbar + Promo Top-Bar + Nav Links Fix

#### Current State

```
┌─────────────────────────────────────────────────────────────┐
│ "Satu akun untuk Belanja · Jualan · Antar"  (plain text)   │  ← Promo bar (desktop only)
├─────────────────────────────────────────────────────────────┤
│ [Logo]  [____Search bar____][Cari]  Cara Kerja  Jadi Mitra │  ← Guest navbar
│                                      ↑ dead anchor  ↑ = register
│ [Logo]  [____Search bar____][Cari]  [LocaleToggle][Role][Dashboard] │ ← Logged-in
├─────────────────────────────────────────────────────────────┤
│ (flat bottom — no wave, no character)                       │
└─────────────────────────────────────────────────────────────┘
```

#### Target Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│ [Flame icon] Gratis ongkir pesanan pertama  ·  Diskon hingga 20%   │  ← Enhanced promo bar
│              gradient bg: from-primary via-brand to-primary         │     (Lucide Flame icon)
│              text-white text-xs, hidden on mobile                   │     NO emoji
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│ [Logo]  [________Search bar________][Cari]   Katalog   [Masuk][Daftar] │ ← Guest
│                                                ↑ link to /catalog   │
│ [Logo]  [________Search bar________][Cari]   Katalog  [Locale][Role][Dashboard] │ ← Logged-in
│                                                ↑ always visible     │
├─────────────────────────────────────────────────────────────────────┤
│ ╰─〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰〰─╯               │  ← SVG wave
│   fill: primary color, opacity 0.06-0.08                            │     static, no animation
│   height: ~24px, viewBox="0 0 1440 32"                              │
│   Visible on ALL pages that use the Navbar                          │
└─────────────────────────────────────────────────────────────────────┘
```

#### Instruksi Sonnet — Task 1.1

```
FILE: resources/js/components/Navbar.vue

== CHANGE 1: Promo Top-Bar (lines 27-31) ==

Replace the current plain div with:

<div class="hidden border-b border-primary/20 bg-gradient-to-r from-primary via-brand to-primary py-1.5 text-center text-xs font-medium text-white sm:block">
    <div class="mx-auto flex max-w-7xl items-center justify-center gap-2 px-4">
        <Flame class="size-3.5 text-white/90" />
        <span>Gratis ongkir pesanan pertama</span>
        <span class="text-white/40">·</span>
        <span>Diskon hingga 20%</span>
        <span class="text-white/40">·</span>
        <span>Daftar sekarang</span>
    </div>
</div>

Import Flame from '@lucide/vue' in the script section.

NOTE: NO emoji. Use Lucide Flame icon only.

== CHANGE 2: Guest Nav Links (lines 73-86) ==

Replace "Cara Kerja" and "Jadi Mitra" with a single "Katalog" link:

<nav v-if="!auth.isAuthenticated" class="hidden items-center gap-1 lg:flex">
    <Link
        :href="catalogIndex.url()"
        class="rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
    >
        Katalog
    </Link>
</nav>

== CHANGE 3: Logged-in Nav (lines 88-95) ==

Add "Katalog" link before the existing items:

<div v-if="auth.isAuthenticated" class="flex items-center gap-2">
    <Link
        :href="catalogIndex.url()"
        class="hidden rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground lg:inline-flex"
    >
        Katalog
    </Link>
    <LocaleToggle />
    <RoleBadge />
    <Button as-child size="sm">
        <Link :href="dashboard()">{{ t('nav.dashboard') }}</Link>
    </Button>
</div>

== CHANGE 4: SVG Wave Divider ==

Add this RIGHT AFTER the closing </header> tag (but still inside the template):

<div class="pointer-events-none relative z-30 -mt-px" aria-hidden="true">
    <svg
        viewBox="0 0 1440 32"
        fill="none"
        preserveAspectRatio="none"
        class="block w-full"
        style="height: 24px"
    >
        <path
            d="M0 16 C240 32 480 0 720 16 C960 32 1200 0 1440 16 L1440 32 L0 32 Z"
            class="fill-primary/[0.07]"
        />
    </svg>
</div>

The wave uses the project's --primary color at 7% opacity.
It's subtle — just enough to give a "sea" character without being garish.
The z-30 keeps it above page content but below the sticky navbar (z-40).
```

---

### 1.2 "Satu Akun, Tiga Cara Bermain" Section — Premium Redesign

#### Current State

```
┌───────────────────────────────────────────────────────────────┐
│              Satu akun, tiga cara bermain                     │
│         Pilih peranmu — bisa ganti kapan saja                 │
│                                                               │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐         │
│  │[icon] Pembeli│ │[icon] Penjual│ │[icon] Kurir  │         │
│  │  tagline     │ │  tagline     │ │  tagline     │         │
│  │  [chevron v] │ │  [chevron v] │ │  [chevron v] │         │
│  │              │ │              │ │              │         │  ← Accordion style
│  │ (click to    │ │              │ │              │         │     hidden features
│  │  expand list)│ │              │ │              │         │
│  └──────────────┘ └──────────────┘ └──────────────┘         │
│                                                               │
│              [Mulai sekarang — gratis]                         │
└───────────────────────────────────────────────────────────────┘
```

#### Target Layout

```
┌───────────────────────────────────────────────────────────────────┐
│                                                                   │
│              Satu Akun, Tiga Cara Bermain                         │
│    ─────────── (gradient teal underline) ───────────              │
│    Daftar sekali, langsung bisa belanja, buka toko,               │
│    atau jadi kurir kampus. Ganti peran kapan saja                 │
│    — saldo tetap satu.                                            │
│                                                                   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐    │
│  │                  │ │                  │ │                  │    │
│  │   ┌──────────┐   │ │   ┌──────────┐   │ │   ┌──────────┐   │ │
│  │   │ Shopping │   │ │   │  Store   │   │ │   │  Truck   │   │ │
│  │   │   Bag    │   │ │   │  icon    │   │ │   │  icon    │   │ │
│  │   └──────────┘   │ │   └──────────┘   │ │   └──────────┘   │ │
│  │   (large icon    │ │   (large icon    │ │   (large icon    │ │
│  │    in gradient    │ │    in gradient    │ │    in gradient    │ │
│  │    circle, blue)  │ │    circle, teal)  │ │    circle, amber)│ │
│  │                  │ │                  │ │                  │ │
│  │   Pembeli        │ │   Penjual        │ │   Kurir          │ │
│  │   Belanja produk │ │   Buka toko dan  │ │   Antar pesanan, │ │
│  │   dari toko      │ │   jual produkmu  │ │   dapat income   │ │
│  │   kampus         │ │                  │ │                  │ │
│  │                  │ │                  │ │                  │ │
│  │   [check] Fitur1 │ │   [check] Fitur1 │ │   [check] Fitur1 │ │
│  │   [check] Fitur2 │ │   [check] Fitur2 │ │   [check] Fitur2 │ │
│  │   [check] Fitur3 │ │   [check] Fitur3 │ │   [check] Fitur3 │ │
│  │   [check] Fitur4 │ │   [check] Fitur4 │ │   [check] Fitur4 │ │
│  │   [check] Fitur5 │ │   [check] Fitur5 │ │   [check] Fitur5 │ │
│  │                  │ │                  │ │                  │ │
│  │  [Mulai Belanja→]│ │  [Buka Toko →]   │ │  [Jadi Kurir →]  │ │
│  │                  │ │                  │ │                  │ │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘    │
│                                                                   │
│  Cards animate in on scroll:                                      │
│  - Staggered fade-up (card 1→2→3, 100ms delay each)              │
│  - Icons scale from 0.85→1.0                                     │
│  - Features stagger-fade 40ms each                                │
│  - Duration: 500ms, easing: cubic-bezier(0.4, 0, 0.2, 1)         │
│  - Mobile: stack vertically, 1 column                             │
│                                                                   │
│  Background: subtle gradient from white to secondary              │
└───────────────────────────────────────────────────────────────────┘
```

#### Instruksi Sonnet — Task 1.2

```
FILE: resources/js/components/landing/RoleCards.vue

COMPLETE REWRITE. Follow the motion-design skill for animation timing.

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check, ShoppingBag, Store, Truck } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';

const sectionRef = ref<HTMLElement | null>(null);
const isVisible = ref(false);
let observer: IntersectionObserver | null = null;

onMounted(() => {
    // Respect reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        isVisible.value = true;
        return;
    }
    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                observer?.disconnect();
            }
        },
        { threshold: 0.15 },
    );
    if (sectionRef.value) observer.observe(sectionRef.value);
});

onUnmounted(() => observer?.disconnect());

const roles = [
    {
        key: 'buyer',
        icon: ShoppingBag,
        title: 'Pembeli',
        tagline: 'Belanja produk dari toko kampus',
        gradient: 'from-blue-500 to-blue-600',
        lightBg: 'bg-blue-50',
        iconColor: 'text-blue-500',
        features: [
            'Jelajahi katalog produk aktif',
            'Tambah ke keranjang & checkout',
            'Lacak status pesanan real-time',
            'Top-up wallet & bayar via saldo',
            'Riwayat transaksi lengkap',
        ],
        cta: 'Mulai Belanja',
    },
    {
        key: 'seller',
        icon: Store,
        title: 'Penjual',
        tagline: 'Buka toko dan jual produkmu',
        gradient: 'from-primary to-brand',
        lightBg: 'bg-primary/5',
        iconColor: 'text-primary',
        features: [
            'Buat & kelola toko sendiri',
            'Upload produk dengan foto',
            'Kelola stok & harga',
            'Proses pesanan masuk',
            'Pantau laporan penjualan',
        ],
        cta: 'Buka Toko',
    },
    {
        key: 'driver',
        icon: Truck,
        title: 'Kurir',
        tagline: 'Antar pesanan, dapat penghasilan',
        gradient: 'from-amber-500 to-amber-600',
        lightBg: 'bg-amber-50',
        iconColor: 'text-amber-500',
        features: [
            'Lihat pesanan siap antar',
            'Ambil & selesaikan pengiriman',
            'Penghasilan langsung ke wallet',
            'Riwayat pengiriman & pendapatan',
            'Kerja fleksibel antar kuliah',
        ],
        cta: 'Jadi Kurir',
    },
];
</script>

<template>
    <section
        ref="sectionRef"
        id="jadi-mitra"
        class="bg-gradient-to-b from-background to-secondary/30 py-16 sm:py-20"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl lg:text-4xl">
                    Satu Akun, Tiga Cara Bermain
                </h2>
                <div class="mx-auto mt-3 h-1 w-20 rounded-full bg-gradient-to-r from-primary to-brand" />
                <p class="mx-auto mt-4 max-w-xl text-muted-foreground">
                    Daftar sekali, langsung bisa belanja, buka toko, atau jadi kurir kampus.
                    Ganti peran kapan saja — saldo tetap satu.
                </p>
            </div>

            <!-- Role Cards Grid -->
            <div class="grid gap-6 sm:grid-cols-3">
                <div
                    v-for="(role, index) in roles"
                    :key="role.key"
                    class="role-card group flex flex-col rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-lg"
                    :class="{ visible: isVisible }"
                    :style="{ animationDelay: `${index * 100}ms` }"
                >
                    <!-- Large Icon -->
                    <div class="mb-6 flex justify-center">
                        <div
                            class="flex size-20 items-center justify-center rounded-2xl bg-gradient-to-br shadow-lg transition-transform duration-300 group-hover:scale-105"
                            :class="role.gradient"
                        >
                            <component :is="role.icon" class="size-10 text-white" />
                        </div>
                    </div>

                    <!-- Title + Tagline -->
                    <div class="mb-5 text-center">
                        <h3 class="text-xl font-bold text-foreground">{{ role.title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ role.tagline }}</p>
                    </div>

                    <!-- Feature List (always visible, no accordion) -->
                    <ul class="mb-6 flex-1 space-y-2.5">
                        <li
                            v-for="(feat, fi) in role.features"
                            :key="feat"
                            class="role-feature flex items-start gap-2.5 text-sm text-muted-foreground"
                            :class="{ visible: isVisible }"
                            :style="{ animationDelay: `${index * 100 + fi * 40 + 200}ms` }"
                        >
                            <span
                                class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full"
                                :class="role.lightBg"
                            >
                                <Check class="size-3" :class="role.iconColor" />
                            </span>
                            {{ feat }}
                        </li>
                    </ul>

                    <!-- CTA Button -->
                    <Button as-child variant="outline" class="w-full gap-2 border-primary/20 text-primary hover:bg-primary hover:text-white">
                        <Link :href="register()">
                            {{ role.cta }}
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    from { opacity: 0; transform: translateX(-8px); }
    to { opacity: 1; transform: translateX(0); }
}

.role-card {
    opacity: 0;
    transform: translateY(30px);
}

.role-card.visible {
    animation: slide-up 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.role-feature {
    opacity: 0;
    transform: translateX(-8px);
}

.role-feature.visible {
    animation: fade-in 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@media (prefers-reduced-motion: reduce) {
    .role-card,
    .role-feature {
        opacity: 1 !important;
        transform: none !important;
        animation: none !important;
    }
}
</style>

IMPORTANT NOTES:
- NO emoji anywhere. All icons are from @lucide/vue.
- Motion personality: Premium (350-600ms, cubic-bezier(0.4,0,0.2,1), no overshoot)
- IntersectionObserver triggers animations when section scrolls into view
- prefers-reduced-motion is respected — instant display, no animation
- Feature lists are ALWAYS visible — no accordion click-to-expand
- Mobile: cards stack vertically (grid cols 1), desktop: 3 columns
```

---

### 1.3 Popular Stores Section (New Component)

> [!WARNING]
> **⚠ Koreksi:** route `stores.show` param-nya `{store}` (`routes/web.php:33`). Sebelum pakai `:href="`/stores/${store.slug}`"`, pastikan `Store::getRouteKeyName()` mengembalikan `'slug'`. Kalau masih binding `id`, link rusak → pakai route helper `stores.show` atau ganti ke `store.id`. Query `withCount('products')` sudah menyediakan `products_count`; pastikan `slug` ikut ke-select.

#### Target Layout

```
┌───────────────────────────────────────────────────────────────────┐
│                                                                   │
│   [Store icon] Toko Populer                    Lihat semua [→]   │
│                Dari penjual aktif di kampus                       │
│                                                                   │
│   Mobile (horizontal scroll):                                     │
│   ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐ → scroll          │
│   │  [TB]  │ │  [WM]  │ │  [KS]  │ │  [AE]  │                  │
│   │ Toko   │ │ Warung │ │ Kopi   │ │ Alat   │                  │
│   │ Berkah │ │ Mama   │ │ Susu   │ │ Elektr │                  │
│   │ 3 prod │ │ Lia    │ │ Jaya   │ │ onik   │                  │
│   │        │ │ 3 prod │ │ 5 prod │ │ 2 prod │                  │
│   └────────┘ └────────┘ └────────┘ └────────┘                  │
│                                                                   │
│   Desktop (grid 4 cols):                                          │
│   ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌────────┐│
│   │  [TB]        │ │  [WM]        │ │  [KS]        │ │  [AE]  ││
│   │              │ │              │ │              │ │        ││
│   │ Toko Berkah  │ │ Warung Mama  │ │ Kopi Susu    │ │ Alat   ││
│   │ 3 produk     │ │ Lia          │ │ Jaya         │ │ Elektr ││
│   │              │ │ 3 produk     │ │ 5 produk     │ │ 2 prod ││
│   └──────────────┘ └──────────────┘ └──────────────┘ └────────┘│
│                                                                   │
│   [TB] = Initial avatar circle with gradient bg                   │
│   (hash store name to pick gradient from a preset list)           │
│   Each card: rounded-xl, border, hover:shadow-md + scale(1.02)   │
│   Link: each card goes to /stores/{slug}                          │
└───────────────────────────────────────────────────────────────────┘
```

#### Instruksi Sonnet — Task 1.3

```Shell
== BACKEND ==

FILE: app/Http/Controllers/Web/HomeController.php

Add to the index() method — query popular stores:

use App\Models\Store;

// In the index method, add:
$popularStores = Store::withCount('products')
    ->having('products_count', '>', 0)
    ->orderByDesc('products_count')
    ->limit(8)
    ->get(['id', 'name', 'slug']);

// Pass to Inertia render:
return Inertia::render('Welcome', [
    'featured' => $featured,        // existing
    'reviews' => $reviews,          // existing
    'popularStores' => $popularStores,  // NEW
]);

== FRONTEND ==

FILE: resources/js/components/landing/PopularStores.vue (NEW FILE)

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Store as StoreIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { index as catalogIndex } from '@/routes/catalog';
// Import the store show route if available, otherwise use raw URL

interface PopularStore {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

defineProps<{ stores: PopularStore[] }>();

// Generate a deterministic gradient from store name
const gradients = [
    'from-blue-400 to-blue-600',
    'from-primary to-brand',
    'from-amber-400 to-amber-600',
    'from-rose-400 to-rose-600',
    'from-violet-400 to-violet-600',
    'from-emerald-400 to-emerald-600',
    'from-cyan-400 to-cyan-600',
    'from-orange-400 to-orange-600',
];

function storeGradient(name: string): string {
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return gradients[Math.abs(hash) % gradients.length];
}

function storeInitials(name: string): string {
    return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
}
</script>

<template>
    <section v-if="stores.length > 0" class="border-b border-border py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <StoreIcon class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                            Toko Populer
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Dari penjual aktif di kampus
                        </p>
                    </div>
                </div>
                <Button as-child variant="ghost" size="sm" class="gap-1 text-primary hover:text-primary">
                    <Link :href="catalogIndex.url()">
                        Lihat semua
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>

            <div class="no-scrollbar -mx-4 flex gap-4 overflow-x-auto px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:grid lg:grid-cols-4 lg:overflow-visible lg:px-0">
                <Link
                    v-for="store in stores"
                    :key="store.id"
                    :href="`/stores/${store.slug}`"
                    class="group flex w-40 shrink-0 flex-col items-center gap-3 rounded-xl border border-border bg-card p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md lg:w-auto"
                >
                    <div
                        class="flex size-14 items-center justify-center rounded-xl bg-gradient-to-br text-lg font-bold text-white shadow-sm transition-transform duration-200 group-hover:scale-105"
                        :class="storeGradient(store.name)"
                    >
                        {{ storeInitials(store.name) }}
                    </div>
                    <div>
                        <p class="font-medium text-foreground line-clamp-1">{{ store.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ store.products_count }} produk</p>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>

---

FILE: resources/js/pages/Welcome.vue

Update to include PopularStores:

import PopularStores from '@/components/landing/PopularStores.vue';

// Add to defineProps:
defineProps<{
    featured: Product[];
    reviews: Review[];
    popularStores: PopularStore[];  // ADD THIS
}>();

// Update template order:
<HeroSection />
<TrustBand />
<PopularStores :stores="popularStores" />   <!-- NEW: after TrustBand -->
<FeaturedStrip :products="featured" />
<RoleCards />
<ReviewsSection :reviews="reviews" />
```

---

### 1.4 Buyer Post-Login Redirect to Catalog

> [!WARNING]
> **⚠ Koreksi penting:** **TIDAK ADA kolom `active_role`** di model User. Active role di-resolve server-side via `RoleService::resolveActiveRole($request)` (lihat `HandleInertiaRequests.php:50`). Snippet di bawah yang pakai `$user->active_role === 'buyer'` **akan error**. Ganti jadi:
> ```php
> $active = app(\App\Services\RoleService::class)->resolveActiveRole($request)?->value;
> if ($active === \App\Enums\RoleName::Buyer->value) {
>     return redirect()->route('catalog.index');
> }
> ```
> Terapkan pola yang sama di `DashboardController` **dan** `RoleController`. Jangan baca role dari request body (golden rule 4).

#### Instruksi Sonnet — Task 1.4

```
FILE: app/Http/Controllers/Web/DashboardController.php

In the index() method, add a redirect for buyers:

public function index(Request $request)
{
    $user = $request->user();

    // Buyer lands on catalog by default — dashboard still accessible via nav
    if ($user->active_role === 'buyer') {
        return redirect()->route('catalog.index');
    }

    // ... rest of existing dashboard logic for admin/seller/driver
}

---

FILE: app/Http/Controllers/Web/RoleController.php

In the store() method (after setting the active role), change the redirect:

// After setting active role...
if ($activeRole === 'buyer') {
    return redirect()->route('catalog.index');
}

return redirect()->route('dashboard');

IMPORTANT: Do NOT remove the Buyer dashboard page or its route.
Buyers can still visit /dashboard directly — the sidebar/bottom nav
will link to it. This only changes the DEFAULT landing after login/role-select.
```

---

### 1.5 Mobile Bottom Navigation — Role-Aware

> [!WARNING]
> **⚠ Koreksi:** ambil role aktif dari shared prop **`auth.activeRole`** (di-share di `HandleInertiaRequests.php:47-50`), **bukan** `user.active_role`. Tab "Keranjang" (buyer) butuh badge count — sediakan via shared prop atau computed dari cart store; jangan hardcode.

#### Target Layout Per Role

```
GUEST:
┌───────────────────────────────────────────────────────┐
│  [Home]     │    [Search]    │     [User]             │
│  Home icon  │  Search icon   │   User icon            │
│             │   Katalog      │    Masuk               │
└───────────────────────────────────────────────────────┘

BUYER:
┌───────────────────────────────────────────────────────────────┐
│  [Home]    │   [Search]   │  [ShoppingCart]  │    [User]      │
│  Home icon │  Search icon │  Cart icon       │   User icon    │
│  Home      │  Katalog     │  Keranjang       │   Profil       │
│            │              │  (badge: count)  │                │
└───────────────────────────────────────────────────────────────┘

SELLER:
┌───────────────────────────────────────────────────────────────┐
│  [Layout]  │  [Package]   │  [Clipboard]  │    [User]        │
│  Dashboard │  Produk      │  Pesanan      │   Profil         │
│  icon      │  icon        │  icon         │   icon           │
└───────────────────────────────────────────────────────────────┘

DRIVER:
┌───────────────────────────────────────────────────────────────┐
│  [Layout]     │    [Truck]     │      [User]                  │
│  Dashboard    │    Jobs        │      Profil                  │
│  icon         │    icon        │      icon                    │
└───────────────────────────────────────────────────────────────┘

ADMIN:
┌───────────────────────────────────────────────────────────────┐
│  [Layout]  │  [Clipboard]  │   [Tag]     │    [User]         │
│  Dashboard │  Orders       │  Discounts  │   Profil          │
│  icon      │  icon         │  icon       │   icon            │
└───────────────────────────────────────────────────────────────┘

Style:
- Height: h-16
- Position: fixed inset-x-0 bottom-0 z-40
- Background: bg-white/95 backdrop-blur-sm border-t border-border
- Active item: text-primary font-medium
- Inactive: text-muted-foreground/70
- Show on mobile only: min-[769px]:hidden
- Safe area: pb-safe (for phones with home bar)
```

#### Instruksi Sonnet — Task 1.5

```
FILE: resources/js/components/BottomNav.vue

COMPLETE REWRITE with role-aware navigation.

Import route helpers:
- home, dashboard, login from '@/routes'
- catalogIndex from '@/routes/catalog'
- editProfile from '@/routes/profile'
- Import relevant role-specific routes

Use useAuthStore() to get isAuthenticated and activeRole.

Use computed() to return different nav items per role (see layouts above).

ALSO: Ensure BottomNav renders on GuestLayout too.

FILE: resources/js/layouts/GuestLayout.vue
Add <BottomNav /> at the bottom of the template so guests see it too.

FILE: resources/js/layouts/app/AppShellLayout.vue (or wherever DashboardLayout renders)
Ensure BottomNav is included here as well.
```

---

## TIER 2: Important — 29 Juni

---

### 2.1 Admin Monitoring Pages (Level 6A — 3 pts)

> [!WARNING]
> Without these pages, you lose 3 points on Level 6A. The spec explicitly requires: monitoring for users, stores, products, orders, deliveries, overdue.

#### Admin Sidebar Target Layout

```
ADMIN SIDEBAR:
┌────────────────────┐
│ [LayoutDashboard]  │
│  Dashboard         │
├────────────────────┤
│ [Users]            │
│  Pengguna          │
│ [Store]            │
│  Toko              │
│ [Package]          │
│  Produk            │
│ [ClipboardList]    │
│  Pesanan           │
│ [Truck]            │
│  Pengiriman        │
│ [AlertTriangle]    │
│  Overdue           │
├────────────────────┤
│ [Tag]              │
│  Promo             │
│ [Ticket]           │
│  Voucher           │
├────────────────────┤
│ [Settings]         │
│  Pengaturan        │
└────────────────────┘
```

#### Each Admin Page Layout

```
┌─────────────────────────────────────────────────────────────┐
│  [icon] Page Title                         [Search box]     │
│                                                             │
│  ┌──────┬───────────┬──────────┬─────────┬───────────┐     │
│  │ ID   │ Name      │ Detail   │ Status  │ Date      │     │
│  ├──────┼───────────┼──────────┼─────────┼───────────┤     │
│  │ 1    │ John Doe  │ buyer    │ [badge] │ 2026-06-1 │     │
│  │ 2    │ Jane Doe  │ seller   │ [badge] │ 2026-06-2 │     │
│  │ ...  │ ...       │ ...      │ ...     │ ...       │     │
│  └──────┴───────────┴──────────┴─────────┴───────────┘     │
│                                                             │
│  Showing 1-10 of 25        [< 1 2 3 >] pagination          │
└─────────────────────────────────────────────────────────────┘
```

#### Instruksi Sonnet — Task 2.1

```
Create 6 controller + 6 pages + update routes + update sidebar.

BACKEND — Create simple controllers in app/Http/Controllers/Web/Admin/:
- AdminUserController.php     → paginate User::with roles info
- AdminStoreController.php    → paginate Store::withCount('products')
- AdminProductController.php  → paginate Product::with('store')
- AdminOrderController.php    → paginate Order::with('buyer','store') + status filter
- AdminDeliveryController.php → paginate Delivery::with('driver','order')
- AdminOverdueController.php  → query overdue-eligible orders

Each controller: simple index() that paginates and returns Inertia::render.

ROUTES — Add to the admin middleware group in routes/web.php.

FRONTEND — Create pages in resources/js/pages/admin/:
- users/Index.vue
- stores/Index.vue
- products/Index.vue
- orders/Index.vue
- deliveries/Index.vue
- overdue/Index.vue

Each page uses shadcn Table component with:
- Column headers
- Data rows with relevant info
- Badge for status fields
- Simple search input
- Pagination

SIDEBAR — Update AppSidebar.vue to include the new admin menu items
when the user role is admin.
```

---

### 2.2 Catalog Search/Filter + Product Card Improvement

#### Target Catalog Layout

```
┌────────────────────────────────────────────────────────────────────┐
│ Search bar (prominent, full-width on mobile)                       │
│ [Search icon] [________________Search________________] [Cari]     │
│                                                                    │
│ Sort: [Terbaru v]     Showing 12 of 48 products                   │
│                                                                    │
│ Mobile (2 columns):          Desktop (4 columns):                  │
│ ┌─────────┐ ┌─────────┐    ┌────────┐ ┌────────┐ ┌────────┐ ┌──┐│
│ │ [image] │ │ [image] │    │[image] │ │[image] │ │[image] │ │  ││
│ │ [store] │ │ [store] │    │[store] │ │[store] │ │[store] │ │  ││
│ │ Name    │ │ Name    │    │ Name   │ │ Name   │ │ Name   │ │  ││
│ │ Rp xxx  │ │ Rp xxx  │    │Rp xxx  │ │Rp xxx  │ │Rp xxx  │ │  ││
│ └─────────┘ └─────────┘    └────────┘ └────────┘ └────────┘ └──┘│
│ ┌─────────┐ ┌─────────┐                                          │
│ │ ...     │ │ ...     │                                          │
│ └─────────┘ └─────────┘                                          │
│                                                                    │
│ [< 1 2 3 ... >] pagination                                       │
└────────────────────────────────────────────────────────────────────┘

Product Card Detail:
┌──────────────────┐
│ ┌──────────────┐ │
│ │              │ │ ← aspect-square image
│ │   [image]    │ │    hover: scale(1.05)
│ │              │ │    if stock=0: "Stok habis" badge
│ └──────────────┘ │
│ [Store badge]    │ ← small, secondary variant
│ Product Name     │ ← line-clamp-2, font-medium
│ Rp 45.000        │ ← font-semibold, text-primary
└──────────────────┘
```

#### Instruksi Sonnet — Task 2.2

```
FILE: resources/js/pages/catalog/Index.vue

CHANGES:
1. Change grid from "sm:grid-cols-2 lg:grid-cols-3" to:
   "grid-cols-2 sm:grid-cols-3 lg:grid-cols-4"
   (2 cols on mobile — this is the biggest responsive fix)

2. Add sort dropdown above the grid:
   - "Terbaru" (default, by created_at desc)
   - "Harga Terendah" (price asc)
   - "Harga Tertinggi" (price desc)

   Use shadcn Select component.
   On change, add ?sort=price_asc (or similar) to the URL and
   let the backend handle sorting.

3. Backend: Update CatalogController::index() to accept ?sort parameter

4. Product count: Show "Menampilkan X dari Y produk" text

5. Make search bar sticky on scroll (add sticky top-16 to the search area)
```

---

### 2.3 Profile Page Overhaul

#### Target Profile Layout

```
┌────────────────────────────────────────────────────────────────┐
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  ┌────────┐                                              │ │
│  │  │  [FM]  │  Farros Maulana                              │ │
│  │  │ avatar │  farros@email.com                            │ │
│  │  │ circle │  [Buyer badge] [Seller badge] [Driver badge] │ │
│  │  └────────┘  Active: Buyer    [Switch Role]              │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  Informasi Profil                                        │ │
│  │                                                          │ │
│  │  Nama     [________________________]                     │ │
│  │  Email    [________________________]                     │ │
│  │  Phone    [________________________]  (if exists)        │ │
│  │                                                          │ │
│  │  [Save]                                                  │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  Keamanan                                                │ │
│  │                                                          │ │
│  │  Password lama  [____________]                           │ │
│  │  Password baru  [____________]                           │ │
│  │  Konfirmasi     [____________]                           │ │
│  │                                                          │ │
│  │  [Update Password]                                       │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  (role-specific section — shown based on active role)    │ │
│  │  Buyer: Saldo wallet, alamat default                     │ │
│  │  Seller: Nama toko, jumlah produk                        │ │
│  │  Driver: Total penghasilan, jobs selesai                  │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  Hapus Akun (destructive section, red border)            │ │
│  │  [Hapus Akun Saya]                                       │ │
│  │  (hidden for admin)                                      │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

### 2.4 Seller Product Card & Form Polish (Visual)

> Fungsi category picker + validasi sudah dikerjakan di **§1.0c**. Section ini fokus **tampilan**: card produk seller + layout form. Ikuti design skill (`ui-ux-pro-max` + `frontend-design`) — bukan default shadcn polos.

#### Seller Product List — `resources/js/pages/seller/products/Index.vue`

```
Card produk baru (grid responsif: grid-cols-2 sm:grid-cols-3 lg:grid-cols-4):
┌────────────────────┐
│ ┌────────────────┐ │  ← gambar aspect-square, object-cover
│ │    [image]     │ │     stok 0 → overlay badge "Stok habis" (destructive)
│ └────────────────┘ │
│ [Badge kategori]   │  ← sub-kategori, variant secondary, size kecil
│ Nama produk        │  ← line-clamp-2, font-medium
│ Rp 45.000          │  ← font-semibold text-primary
│ Stok: 12  [● Aktif]│  ← chip stok (warna beda kalau 0) + toggle is_active
│ [Edit]   [Hapus]   │  ← aksi (Hapus → konfirmasi Dialog)
└────────────────────┘
Empty state rapi: ikon Package + "Belum ada produk" + tombol "Tambah Produk".
```

#### Seller Product Form — `resources/js/pages/seller/products/Form.vue`

```
Form bersection (Card per section, bukan satu blok panjang):
  1. Info Produk   : Nama*, Deskripsi
  2. Kategori      : Induk* → Sub* (cascading select dari §1.0c)
  3. Harga & Stok  : Harga* (prefix "Rp", integer), Stok* (integer)
  4. Gambar        : upload + PREVIEW thumbnail + cek ukuran client-side (≤2MB)
                     → kalau kebesaran: toast "Ukuran gambar maksimal 2MB" + reject sebelum submit
Semua label wajib → RequiredMark (*). InputError di tiap field.
Tombol submit: disabled + spinner saat form.processing.
```

DoD: `pint` + lint hijau · responsif 360/768/1280/1920 · commit `feat(product): redesign seller product card and form`.

---

## TIER 3: Final Day — 30 Juni

### 3.1 README Rewrite

#### Target Structure

```
# SEAPEDIA
Multi-Role Campus Marketplace — COMPFEST 18 SE Academy

## Quick Start (Plain PHP — Recommended)
[detailed step-by-step, 10 steps]

## Alternative: Docker Setup
[5 steps]

## Demo Accounts
[table — same as current]

## Features Implemented
[per-level checklist]

## Architecture
[brief ascii diagram]

## Business Rules
[single-store cart, PPN 12%, delivery, driver earning, overdue]

## Security
[SQLi, XSS, CSRF, RBAC — brief]

## API Documentation
[Swagger URL]

## Testing & Formatting
[commands]

## Production Deployment
[Docker prod — keep existing]
```

**JANGAN** sebut referensi e-commerce lain di README. Hapus semua sprint logs dan internal notes.

> **Status Level 7C (3 pts) — hasil audit (28 Juni): ~70% jadi, tinggal rapikan.**
> - ✅ README sudah dokumentasikan single-store, PPN 12% base, discount-before-PPN, driver earning 80%, overdue SLA + time-sim, demo accounts.
> - ✅ Demo seeder lengkap; Swagger UI di `/api/documentation` (33 path); Postman specs ada.
> - ⚠️ **Buang 31 penyebutan "sprint"** di README (`grep -ni sprint README.md`) + hilangkan catatan carry-over internal.
> - ⚠️ **Tambah bagian baru**: "Kategori Produk" (hierarki, admin-managed) + "Security" (rangkum hasil §3.3: SQLi/XSS/validasi/RBAC/session).
> - ⚠️ **Swagger statis**: jangan regenerate; tambah path `/api/v1/categories` ke `api-docs.json` manual bila endpoint API kategori dibuat.

### 3.2 CLAUDE.md Update

Hapus referensi sprint planning, update status. **JANGAN** sebut referensi e-commerce lain.

### 3.3 Security Hardening & Validation Pass (Level 7A — 4 pts, GRADED)

> **Status hasil audit (28 Juni):** pondasi sudah aman secara struktur — tinggal **mengetatkan bound** + lapisan UX, **bukan bikin dari nol**.
> - ✅ **XSS aman**: `v-html` **nol** di seluruh frontend; komentar review dirender plain-text.
> - ✅ **SQLi aman**: `DB::raw`/`whereRaw` **nol**; semua Eloquent.
> - ✅ **Validasi ADA**: 25 FormRequest + registrasi via Fortify (`PasswordValidationRules`/`ProfileValidationRules`).
> - ⚠️ **Bound longgar** — yang perlu dirapikan (lihat matriks di bawah).

#### A. Tighten FormRequest yang sudah ada (server-side = batas keamanan)

| FormRequest | Field → aturan yang dirapikan |
| --- | --- |
| `CreateNewUser`/Fortify concerns | name `required\|string\|min:2\|max:100`; email `required\|email\|max:255\|unique`; username `alpha_dash\|min:3\|max:30\|unique`; phone `nullable\|regex digit\|max:20`; password `min:8\|confirmed` |
| `StoreProductRequest`/`UpdateProductRequest` | name `max:255`→**`max:150`**; price `min:0`→**`min:100\|max:100000000`**; stock tambah **`max:1000000`**; **tambah `category_id required\|exists`**; image sudah `image\|mimes\|max:2048` ✅ |
| `StoreAppReviewRequest` | reviewer_name `max:255`→**`max:80`**; rating `between:1,5` ✅; comment `max:1000` ✅ |
| `StoreStoreRequest`/`UpdateStoreRequest` | name `required\|min:3\|max:100\|unique`; description `nullable\|max:500` |
| `StoreAddressRequest`/`UpdateAddressRequest` | recipient `max:100`; phone regex digit `max:20`; address `max:500`; label `max:50` |
| `StoreTopupRequest` | amount `integer\|min:10000\|max:100000000` |
| `StoreCheckoutRequest`/`PreviewCheckoutRequest` | address_id `exists`; delivery_method `Rule::in(enum)`; discount_code `nullable\|string\|max:50` |
| `StoreVoucherRequest`/`StorePromoRequest` | code `max:50\|unique`; value/percent range wajar; expiry `date\|after:today`; quota `integer\|min:1` |
| `StoreCategoryRequest`/`UpdateCategoryRequest` (baru §1.0b) | name `min:2\|max:80`; parent_id `nullable\|exists`; icon `max:50` |

Setiap request beri pesan `messages()` ramah berbahasa Indonesia (mis. "Harga minimal Rp100").

#### B. Lapisan UX (lihat Confirmed Decisions)

```
- RequiredMark (*) di label semua field wajib di SEMUA form.
- InputError per-field di semua form (komponen sudah ada: resources/js/components/InputError.vue).
- Toast vue-sonner untuk flash global: mount <Toaster/> di layout root; share 'flash' (success/error)
  di HandleInertiaRequests; tampilkan toast pada error/success.
  · Login salah → field error "Email atau password salah."
  · Upload kebesaran → cek client-side SEBELUM submit + toast "Ukuran gambar maksimal 2MB."
- Cermin client-side: atribut maxlength pada input sesuai bound server.
```

#### C. RBAC / session (Level 7B) — verifikasi (bukan bikin baru)

```
- Logout invalidate session/token. ✅ cek.
- Endpoint privat tak bisa diakses dengan ganti route frontend → uji manual + Policy aktif.
- Active role diverifikasi server-side (EnsureActiveRole) untuk semua aksi role.
- Pastikan kategori admin-only: AdminCategoryController di grup middleware admin.
```

#### 3.4 Responsive QA

Uji manual di **360 / 768 / 1280 / 1920px** (target demo 1920). Fokus: navbar/bottom-nav, grid katalog 2-kolom mobile, filter kategori (scroll chip), form produk, tabel admin (overflow-x-auto). Playwright dilewati sesuai keputusan — catat di `progress.md`.

---

## Execution Timeline

| Waktu                   | Task                                          | Est.    |
| ----------------------- | --------------------------------------------- | ------- |
| **28 Juni Malam** | **1.0a/b Kategori: schema+model+service+seed+admin CRUD** | 2.5 jam |
|                         | **1.0c/d Kategori: seller picker + 4 surface UI**         | 2.5 jam |
|                         | 1.1 Wave Navbar + Promo bar                   | 1 jam   |
| **29 Juni Pagi**  | 1.2 RoleCards premium redesign                | 1.5 jam |
|                         | 1.4 Buyer redirect (⚠ pakai RoleService)     | 30 min  |
|                         | 1.5 Bottom Nav per role (⚠ auth.activeRole)  | 1.5 jam |
|                         | 1.3 Popular Stores section                    | 1.5 jam |
| **29 Juni Sore**  | 2.1 Admin monitoring (6 pages)                | 4 jam   |
|                         | 2.2 Catalog improvements (search/sort/filter) | 2 jam   |
| **30 Juni Pagi**  | 2.4 Seller product card & form polish         | 2 jam   |
|                         | 2.3 Profile overhaul                          | 2 jam   |
|                         | 3.3 Validation pass (tighten + RequiredMark + toast) | 2.5 jam |
| **30 Juni Sore**  | 3.1 README rewrite + 3.2 CLAUDE.md            | 1.5 jam |
|                         | 3.4 Responsive QA + fixes (360/768/1280/1920) | 2.5 jam |
|                         | Final commit + push                           | 30 min  |

**Total: ~30 jam kerja dalam ~46 jam.** Kategori naik ke prioritas teratas (foundation). Kalau waktu mepet, urutan korban (dari paling boleh dipotong): 1.3 Popular Stores → animasi premium 1.2 → halaman kategori khusus (1.0d#4, filter katalog sudah menutupi). **Jangan korbankan 3.3 (graded) & 1.0a (foundation).**
