# Extended Development Plan & Progress

## Progress: Phase 4 (Seller Dashboard)
- **Database:** Ditambahkan tabel `product_variants` dan `product_images`. Tabel `products` mempertahankan `price` dan `stock` sebagai base/summary value.
- **Requests:** `StoreProductRequest` dan `UpdateProductRequest` dimodifikasi agar menerima `has_variants`, array `variants[]`, array `images[]` (multiple files), dan `deleted_image_ids[]`. `image` tunggal diganti menjadi array of images.
- **Service:** `ProductService` dimodifikasi agar menangani `product_variants` dan `product_images` atomik di dalam satu transaksi, menghapus gambar lama yang tidak dipakai, dan menghitung `base_price` dari harga varian termurah.
- **Frontend UI:** `Form.vue` pada Seller Product dirubah menggunakan UI multi-upload gambar dan variant builder menggunakan `v-for`.
- **Status:** **DONE**. Code telah di-commit ke Git.

## Progress: Bug Fixes & UI Polish
- **Auth Translation:** Menambahkan translasi untuk `auth.failed` dan `auth.password` di `lang/id.json` dan `lang/en.json` agar memunculkan pesan validasi lokal alih-alih key mentahnya.
- **Navbar Cart Icon:** Menambahkan ikon Keranjang dengan *badge* notifikasi (maksimum `10+`) di _Navbar_ untuk role _Buyer_, di mana perhitungan notifikasinya berdasarkan *unique cards/items* dari relasi `User::cart()`.
- **Checkout Query Fix:** Memperbaiki bug error `Unknown column 'image_path'` pada saat keranjang/checkout diakses dengan menghapus pemanggilan `image_path` pada relasi `variant`.
- **Catalog Show UI:** Memperbaiki layout tombol "Keranjang" dan "Beli Langsung" menjadi *flex-1* agar luasnya terbagi rata secara 50/50. Memindahkan bagian *Info Toko* agar tampil di atas bagian pemilihan varian produk.
- **Profile SSR Fix:** Mengganti pemanggilan rute global `route('password.update')` (yang *error* pada *Inertia SSR* karena bukan *Ziggy*) dengan *auto-routing* bawaan *Wayfinder*: `SecurityController.update.form()`.
- **Status:** **DONE**. Code telah di-commit ke Git secara keseluruhan.
