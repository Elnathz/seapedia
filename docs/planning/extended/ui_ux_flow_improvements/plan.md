# Plan: UI/UX & Flow Improvements (Phase 5 & 6)

## Progress Tracker
- [ ] 1. Redirect Login/Register ke Catalog
- [ ] 2. Merapikan Dropdown Profil di Navbar
- [ ] 3. Merapikan Sidebar Dashboard & Logo
- [ ] 4. Peningkatan Fitur Alamat & Pengaturan Profil
- [ ] 6. Peningkatan UI Halaman Pesanan (Buyer Orders)
- [ ] 7. Peningkatan UI Halaman Detail Pesanan (Buyer Order Detail)
- [ ] 8. Peningkatan UI Pesanan Tersedia (Driver Dashboard)
- [ ] 9. Perombakan UI Produk Toko (Seller Dashboard)
- [ ] 10. Admin Dashboard: Voucher, Promo & Simulasi Waktu (Level 6B & 6C)
- [ ] 11. Pembaruan README.md & Dokumentasi

---

## Detail Rencana (Sesuai SEAPEDIA_SPEC.md)

### 1. Redirect Login/Register ke Catalog
- **[MODIFY]** `config/fortify.php` atau membuat `LoginResponse` / `RegisterResponse` kustom agar jika user yang *login/register* memiliki *active role* sebagai `buyer`, diarahkan ke `/catalog`.

### 2. Merapikan Dropdown Profil di Navbar
- **[MODIFY]** `resources/js/components/Navbar.vue`: Menghapus tombol ganti *role* dari luar navbar.
- **[MODIFY]** `resources/js/components/UserMenuContent.vue`: Pindahkan logika ganti peran ke dalam dropdown.

### 3. Merapikan Sidebar Dashboard & Logo
- **[MODIFY]** `resources/js/layouts/AppLayout.vue` / `Sidebar.vue`:
  - Ubah Logo menggunakan *wordmark*.
  - Hapus menu "Keranjang" (sudah ada di Navbar).
  - Hapus menu "Alamat" (dipindah ke Settings).
  - Tambahkan menu "Settings" ke sidebar.

### 4. Peningkatan Fitur Alamat & Pengaturan Profil
- **[NEW MIGRATION]** Kolom `province`, `city`, `postal_code` ke tabel `addresses`.
- **[MODIFY]** `app/Models/Address.php` dan Form Request.
- **[MODIFY]** `resources/js/pages/settings/Profile.vue`: Tambahkan *Section* untuk mengelola Alamat.

### 6. Peningkatan UI Halaman Pesanan (Buyer Orders)
- **[MODIFY]** `resources/js/pages/buyer/orders/Index.vue`:
  - *Tabs/Filter* Status Pesanan (Semua, Menunggu Pembayaran, Sedang Dikemas, Dikirim, Selesai, Dibatalkan).
  - Merombak *Card* Pesanan mirip *Megamart*.

### 7. Peningkatan UI Halaman Detail Pesanan (Buyer Order Detail)
- **[MODIFY]** `resources/js/pages/buyer/orders/Show.vue`:
  - Tampilkan foto produk dengan *link* ke katalog.
  - Tampilkan Nama Toko & Nama Driver pengirim.
  - Tambahkan tombol "Beli Lagi" / "Lihat Produk".
  - **[UI FIX]** Perbaiki jarak *Badge* "Status Pengiriman".

### 8. Peningkatan UI Pesanan Tersedia (Driver Dashboard)
- **[MODIFY]** `resources/js/pages/driver/deliveries/Index.vue` & `resources/js/pages/driver/jobs/Show.vue`:
  - *Tabs/Filter* Metode Pengiriman.
  - *Card* lebih *compact*.
  - Tampilkan SLA/Countdown auto-cancel.
  - Banner peringatan (Merah) jika sedang memiliki *Active Job*.

### 9. Perombakan UI Produk Toko (Seller Dashboard)
- **[MODIFY]** `resources/js/pages/seller/products/Index.vue`:
  - *Layout* produk dibuat *compact* per Kategori (mirip *Megamart Admin*).

### 10. Admin Dashboard: Voucher, Promo & Simulasi Waktu (Level 6B & 6C)
- **[MODIFY]** Halaman Admin:
  - Form Voucher & Promo *intuitive* dengan status aktif/kedaluwarsa.
  - Komponen **Time Machine / Advance Clock** untuk simulasi SLA dan memicu laporan *refund* otomatis.

### 11. Pembaruan README.md & Dokumentasi
- **[MODIFY]** `README.md`:
  - Panduan *Setup* (Docker Compose `docker compose up -d`).
  - Kredensial Admin.
  - Penjelasan *Single-Store Checkout* & Pajak PPN 12%.
  - *Deployment Link* & *Security Notes*.
