# Extended Development Plan & Progress

## Progress: Phase 4 (Seller Dashboard)
- **Database:** Ditambahkan tabel `product_variants` dan `product_images`. Tabel `products` mempertahankan `price` dan `stock` sebagai base/summary value.
- **Requests:** `StoreProductRequest` dan `UpdateProductRequest` dimodifikasi agar menerima `has_variants`, array `variants[]`, array `images[]` (multiple files), dan `deleted_image_ids[]`. `image` tunggal diganti menjadi array of images.
- **Service:** `ProductService` dimodifikasi agar menangani `product_variants` dan `product_images` atomik di dalam satu transaksi, menghapus gambar lama yang tidak dipakai, dan menghitung `base_price` dari harga varian termurah.
- **Frontend UI:** `Form.vue` pada Seller Product dirubah menggunakan UI multi-upload gambar dan variant builder menggunakan `v-for`.
- **Status:** **DONE**. Code telah di-commit ke Git.
