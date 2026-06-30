# Plan (FINAL): Product Seeder, Related Products, Security Hardening, Overdue Fix

> **Status:** Semua keputusan dikonfirmasi — siap eksekusi

---

## Keputusan Terkonfirmasi

| # | Keputusan | Pilihan | Catatan |
|---|-----------|---------|---------|
| A | Kategori Populer | **Option A** | Flatten root+child, sort by product count |
| B | Saldo Maks Wallet | **Rp 1.000.000.000 (1 Miliar)** | ⚠️ 1 Triliun terlalu over untuk marketplace demo — aku rekomendasikan 1 Miliar. Max per-topup Rp 10.000.000 |
| C | Store delete + order aktif | **Option A** | Hard block 422 |
| D | Driver resign + delivery aktif | **Option A** | Hard block. Auto-cancel overdue delivery sesuai `OverdueService::sweep()` (sudah ada) |
| E | Hapus akun | **Approved** | Soft delete + anonymize. Buat fitur hapus per-role (belum ada) |
| F | Switch role | **Option A** | EnsureActiveRole middleware enforce per-request ✅ |
| G | Kategori produk seeder | **Sesuaikan** | Tiap produk disesuaikan kategori & varian |

> [!NOTE]
> **Soal max saldo:** 1 Triliun itu setara sekitar USD 60 juta — tidak masuk akal untuk marketplace demo. 1 Miliar (Rp 1.000.000.000) sudah sangat tinggi dan masih masuk akal. Tapi kalau kamu tetap mau 1 Triliun, tinggal bilang, saya akan pakai angka itu.

---

## Execution Order (per commit-message skill)

```
Commit 0: [pending] — commit semua changes yang belum di-commit
Task 1:   fix(admin): add missing Vue ref import on overdue page       ← PALING CEPAT
Task 2:   feat(catalog): flatten all categories for popular section
Task 3:   feat(wallet): add max balance guard on topup (1 Miliar)
Task 4:   feat(security): block store delete if active orders exist
Task 5:   feat(security): block driver role removal if active delivery
Task 6:   feat(auth): add per-role account deletion with anonymize
Task 7:   feat(product): replace seeder with real photo-backed products
Task 8:   feat(catalog): add related products on product detail page
```

---

## TASK 0: Commit Pending

```bash
git add .
git commit -m "feat(catalog,ui): add random product sort and redesign mobile navbar"
```

---

## TASK 1: Fix `/admin/overdue` Page ← PALING CEPAT

**Root cause confirmed:** `ref is not defined` — tidak ada `import { ref } from 'vue'` di file.

#### [MODIFY] `resources/js/pages/admin/overdue/Index.vue`
```diff
+ import { ref } from 'vue';
  import { router } from '@inertiajs/vue3';
```

Build setelah fix, verifikasi halaman load.

**Kegunaan halaman Overdue:**
- Tampilkan order yang melewati SLA (batas waktu pengiriman)
- Admin klik **+1 Hari / +3 Hari** untuk simulasi waktu maju (wajib untuk demo ke juri)
- Setelah clock maju, `OverdueService::sweep()` auto-refund buyer, restore stok, update status ke `Dikembalikan`
- Memenuhi spec: *"The system must include a way to simulate the next day"*

---

## TASK 2: Kategori Populer Flatten (Root + Child)

**Saat ini:** `$this->categories->tree()->map(...)->take(6)` — hanya root.

#### [MODIFY] `app/Services/CategoryService.php`
- Tambah method `popularFlat(int $limit = 6): Collection`

```php
public function popularFlat(int $limit = 6): Collection
{
    return Category::query()
        ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
        ->having('products_count', '>', 0)
        ->orderByDesc('products_count')
        ->limit($limit)
        ->get(['id', 'name', 'slug']);
}
```

#### [MODIFY] `app/Http/Controllers/Web/CatalogController.php`
```diff
- 'popularCategories' => $this->categories->tree()->map(...)->take(6),
+ 'popularCategories' => $this->categories->popularFlat(6),
```

---

## TASK 3: Max Balance Guard Topup

#### [MODIFY] `app/Services/TopupService.php`

```php
public const int MAX_BALANCE        = 1_000_000_000; // 1 Miliar
public const int MAX_PER_TOPUP      = 10_000_000;    // 10 Juta per topup

public function create(User $user, int $amount): Topup
{
    if ($amount > self::MAX_PER_TOPUP) {
        throw ValidationException::withMessages([
            'amount' => ['Topup maksimal Rp 10.000.000 per transaksi.'],
        ]);
    }

    return DB::transaction(function () use ($user, $amount) {
        $wallet = Wallet::query()->lockForUpdate()->findOrFail($user->wallet->id);

        if ($wallet->balance + $amount > self::MAX_BALANCE) {
            throw ValidationException::withMessages([
                'amount' => ['Saldo maksimal adalah Rp 1.000.000.000.'],
            ]);
        }

        // ... rest of create logic
    });
}
```

#### [VERIFY] Topup FormRequest — tambahkan rule `max: 10_000_000` jika belum ada.

---

## TASK 4: Block Store Delete dengan Order Aktif

Cari controller delete store seller:

#### [MODIFY] `app/Http/Controllers/Web/Seller/StoreController.php` (atau equivalent)

```php
public function destroy(Store $store): RedirectResponse
{
    $this->authorize('delete', $store);

    $hasActiveOrders = Order::where('store_id', $store->id)
        ->whereIn('status', [
            OrderStatus::SedangDikemas,
            OrderStatus::MenungguPengirim,
            OrderStatus::SedangDikirim,
        ])
        ->exists();

    if ($hasActiveOrders) {
        return back()->withErrors([
            'store' => 'Tidak dapat menghapus toko saat masih ada pesanan aktif. Tunggu hingga semua pesanan selesai atau di-refund.'
        ]);
    }

    $store->delete();
    return redirect()->route('seller.dashboard');
}
```

---

## TASK 5: Block Driver Role Removal dengan Delivery Aktif

Temukan endpoint remove-role driver:

#### [MODIFY] relevant RoleController atau ProfileController
```php
// Sebelum remove role driver:
$hasActiveDelivery = Delivery::where('driver_id', $user->id)
    ->whereIn('status', [DeliveryStatus::Available, DeliveryStatus::Taken])
    ->exists();

if ($hasActiveDelivery) {
    return back()->withErrors([
        'role' => 'Selesaikan semua pengiriman aktif sebelum melepas role Driver.'
    ]);
}
```

**Catatan:** Auto-cancel overdue delivery sudah ditangani `OverdueService::sweep()` melalui admin clock. Delivery yang melampaui SLA akan auto-cancel saat admin advance day.

---

## TASK 6: Hapus Akun Per-Role

**Fitur baru yang perlu dibuat** (belum ada, Laravel hanya punya hapus akun keseluruhan).

#### [NEW] `app/Http/Controllers/Web/Profile/RoleDeleteController.php`
```php
// DELETE /profile/roles/{role}
// Hapus satu role dari user (bukan hapus seluruh akun)

public function destroy(Request $request, string $role): RedirectResponse
{
    $user = $request->user();
    
    // Guard: driver harus selesaikan delivery
    if ($role === 'driver') { /* check delivery aktif */ }
    
    // Guard: seller harus selesaikan order + withdraw wallet  
    if ($role === 'seller') { /* check order aktif + wallet > 0 */ }
    
    // Guard: buyer harus selesaikan order aktif
    if ($role === 'buyer') { /* check order aktif */ }
    
    $user->roles()->detach($role); // atau sesuai custom role implementation
    
    // Jika menghapus role terakhir → hapus akun (soft delete + anonymize)
}
```

#### [MODIFY] Halaman Profile di Vue — tambah tombol "Lepas Role" per role yang dimiliki

---

## TASK 7: Seed Produk Real

### Inventori Final (dengan kategori dan varian)

| Produk | Harga | Kategori | Varian? |
|--------|-------|----------|---------|
| **Beras** | | | |
| Sania Beras Premium 5 Kg | 85.000 | beras | - |
| Beras BMW Pandan Wangi 5 KG | 62.500 | beras | - |
| Beras Sunrise 10 KG | 120.000 | beras | - |
| **Makanan** | | | |
| Indomie Mix Rasa 1 Dus (40 pcs) | 150.000 | mie-instan | - |
| Indomie Goreng 1 Karton 40 pcs | 150.000 | mie-instan | - |
| Indomie Soto Mie 1 Kardus 40 pcs | 146.000 | mie-instan | - |
| Ransum TNI Set Darurat | 58.500 | makanan-berat | - |
| **Minuman/Kopi** | | | |
| Kopi Luwak White Koffie 9 Sachet | 20.210 | kopi | - |
| Kopi Arabika Gayo Super Premium 200g | 81.960 | kopi | Biji/Bubuk |
| Kopi Tubruk Gadjah Asli 138 Gr | 18.700 | kopi | - |
| Nescafé Classic Bag 90g | 51.000 | kopi | - |
| **Elektronik - Kamera** | | | |
| Panasonic Lumix FZ80D | 6.999.000 | kamera | - |
| **Elektronik - Keyboard** | | | |
| SteelSeries Apex Pro TKL Wireless Gen 3 | 3.299.000 | keyboard | - |
| **Aksesoris HP** | | | |
| Gantungan HP Inisial Liontin | 4.500 | aksesoris-hp | - |
| Gantungan Tali HP Motif Branded Lanyard | 6.110 | aksesoris-hp | - |
| Gantungan HP Aesthetic Girl Lonceng | 7.999 | aksesoris-hp | - |
| Holder HP Motor Universal GUB | 68.310 | aksesoris-hp | - |
| **Smartphone** | | | |
| iPhone 17 (Black) | 17.000.000 | smartphone | Black/White (tiap warna produk berbeda) |
| iPhone 17 (White) | 17.499.000 | smartphone | - |
| iPhone 17 Pro Max | 25.000.000 | smartphone | Blue/White/Orange |
| **Action Figure / Mainan** | | | |
| Action Figure Iron Man Mark 85 4 inci | 59.900 | action-figure | - |
| **Sepeda** | | | |
| Merida Road Bike Reacto 5000 | 27.499.500 | sepeda-road | - |
| Polygon Bend R2 Gravel Urban | 5.500.000 | sepeda-urban | - |
| Sepeda Thrill Oust 1.0 27.5" | 4.449.000 | sepeda-mtb | **Varian: Biru & Hitam** |
| Polygon Siskiu N5 | 3.750.000 | sepeda-mtb | - |
| **Perkakas** | | | |
| Deli Household Tool Set 112 Pcs | 1.805.000 | perkakas | - |
| Tekiro Mechanic Tools Set SC-MT0626 | 1.050.000 | perkakas | - |
| Tactix Set 26 Pcs Perkakas Rumah Tangga | 723.900 | perkakas | - |
| **Pakaian** | | | |
| Kaos Viral Gaji Bercanda Kerja Serius | 99.000 | kaos | S/M/L/XL |
| Gerald Baju Kaos Fire | 48.000 | kaos | - |
| Jagata Coco Top Wanita Cream | 189.000 | pakaian-wanita | - |
| T-Shirt Smile Love Crop Top | 39.450 | pakaian-wanita | - |
| **Makeup (2 toko berbeda per produk)** | | | |
| ESQA Bitty Balm Stick Blush | 69.700 | makeup-blush | - |
| Maybelline Instant Age Rewind Eraser (Toko A) | 100.215 | makeup-concealer | - |
| Maybelline Instant Age Rewind Eraser (Toko B) | 115.000 | makeup-concealer | - |
| Skintific Cover All Perfect Air Cushion (Toko A) | 128.900 | makeup-cushion | - |
| Skintific Cover All Perfect Air Cushion (Toko B) | 135.000 | makeup-cushion | - |
| Barenbliss Lily Makes Luminous Glow Tint | 65.400 | makeup-lip | - |
| Make Over Silky Smooth Translucent Powder (Toko A) | 131.700 | makeup-powder | - |
| Make Over Silky Smooth Translucent Powder (Toko B) | 136.700 | makeup-powder | - |
| **Lainnya** | | | |
| Tenda Camping Dome Waterproof | 250.000 | outdoor | - |
| Kantong Plastik Vakum Pakaian | 15.000 | rumah-tangga | - |
| Pelapis Kabel Insulasi Tahan Panas | 25.000 | perkakas | - |
| Reel Pancing Spinning Premium | 120.000 | olahraga | - |

**Total: ~45 produk nyata berdasarkan foto yang ada**

#### [MODIFY] `database/seeders/StoreProductSeeder.php`
- Hapus semua produk lama yang tidak berdasarkan foto nyata
- Seed store baru untuk makeup (2 toko berbeda: "Cantik Store" & "Glam Beauty") 
- Seed store untuk sepeda ("Sepeda Nusantara")
- Seed store untuk elektronik/gadget ("TechHub Store", "iGadget Center")
- Setiap produk include `image_path` yang menunjuk ke file foto yang ada

---

## TASK 8: Related Products di Detail Produk

#### [MODIFY] `app/Services/CatalogService.php`
```php
/**
 * Produk terkait: dari kategori yang sama atau parent yang sama.
 * Fallback ke random jika kurang dari $limit.
 */
public function related(Product $product, int $limit = 8): Collection
{
    $related = Product::query()
        ->where('is_active', true)
        ->whereHas('store', fn ($q) => $q->where('is_active', true))
        ->where('id', '!=', $product->id)
        ->where(function ($q) use ($product) {
            $q->where('category_id', $product->category_id)
              ->orWhere('category_id', $product->category->parent_id);
        })
        ->inRandomOrder()
        ->limit($limit)
        ->get(['id', 'name', 'slug', 'price', 'image_path', 'category_id', 'store_id']);

    if ($related->count() < $limit) {
        $extra = Product::query()
            ->where('is_active', true)
            ->whereHas('store', fn ($q) => $q->where('is_active', true))
            ->whereNotIn('id', $related->pluck('id')->push($product->id))
            ->inRandomOrder()
            ->limit($limit - $related->count())
            ->get(['id', 'name', 'slug', 'price', 'image_path', 'category_id', 'store_id']);
        
        $related = $related->concat($extra);
    }

    return $related;
}
```

#### [MODIFY] `app/Http/Controllers/Web/CatalogController.php`
```diff
  return Inertia::render('catalog/Show', [
      'product' => $found,
+     'relatedProducts' => $this->catalog->related($found, 8),
  ]);
```

#### [MODIFY] `resources/js/pages/catalog/Show.vue`
- Terima prop `relatedProducts`
- Tambah section "Produk Serupa" di bawah deskripsi
- Gunakan komponen `ProductCard` yang sudah ada

---

## Security Audit Checklist (security-pass skill)

- [ ] `ref is not defined` di overdue page → FIXED (Task 1)
- [ ] Max balance topup → ENFORCED dalam lockForUpdate (Task 3)
- [ ] Store delete guard → ADDED (Task 4)  
- [ ] Driver resign guard → ADDED (Task 5)
- [ ] v-html audit → grep seluruh codebase, tidak boleh di user content
- [ ] whereRaw/DB::raw → pastikan semua parameterized
- [ ] XSS demo → input `<script>alert(1)</script>` di review → Vue auto-escape ✅
- [ ] SQLi demo → `' OR 1=1 --` di search → Eloquent parameterized ✅
- [ ] OverdueService::sweep() → sudah ada lockForUpdate + refunded_at guard ✅
- [ ] EnsureActiveRole middleware → enforce per-request ✅

---

## Verification Plan

### Automated
```bash
php artisan db:seed --class=StoreProductSeeder
php artisan migrate:fresh --seed
```

### Manual
1. `/admin/overdue` — page loads ✅, +1 Hari berfungsi ✅
2. Topup melebihi 1 Miliar → ditolak dengan pesan jelas
3. Seller coba hapus toko dengan order aktif → ditolak
4. Driver coba lepas role saat delivery aktif → ditolak
5. Catalog → Kategori Populer menampilkan child category
6. Detail produk → ada section "Produk Serupa" di bawah
7. Semua foto produk seed tampil dengan benar

---

## TDD Changes Tracker

File: `tdd_changes_tracker.md` — akan diupdate setelah eksekusi dengan:
- Penambahan `MAX_BALANCE` const di WalletService/TopupService
- Tambah `popularFlat()` di CategoryService  
- Tambah `related()` di CatalogService
- Fitur hapus per-role (RoleDeleteController)
