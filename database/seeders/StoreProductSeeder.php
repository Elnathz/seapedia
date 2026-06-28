<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreProductSeeder extends Seeder
{
    public function __construct(
        private readonly StoreService $stores,
        private readonly ProductService $products,
    ) {}

    /**
     * Populate the catalog with 7 stores (seller1..seller7) and 5-8 products
     * each, covering multiple categories with varied prices and stock levels
     * (including some out-of-stock items to demo that state). Uses
     * StoreService/ProductService so slug uniqueness follows the same rules
     * as a real seller would (§7).
     *
     * Seller1 and multi1 are seeded here with 3 products each. Extended
     * sellers (2-7) get 5-8 products each across different categories.
     */
    public function run(): void
    {
        // seller1 + multi1 — 3 products each (called first, store doesn't exist yet).
        $this->seedStore(
            username: 'seller1',
            storeName: 'Toko Berkah',
            description: 'Toko kelontong dan jasa cetak di lingkungan kampus.',
            products: [
                ['name' => 'Kopi Susu Gula Aren', 'description' => 'Kopi susu segar dengan gula aren asli, diseduh setiap pagi.', 'price' => 18_000, 'stock' => 24, 'category' => 'kopi'],
                ['name' => 'Fotokopi & Print Dokumen', 'description' => 'Layanan fotokopi dan print per halaman, hitam-putih maupun warna.', 'price' => 500, 'stock' => 999, 'category' => 'umum'],
                ['name' => 'Stiker Custom', 'description' => 'Stiker vinyl custom sesuai desain pesanan, tahan air.', 'price' => 10_000, 'stock' => 60, 'category' => 'umum'],
            ],
        );

        $this->seedStore(
            username: 'multi1',
            storeName: 'Warung Mama Lia',
            description: 'Warung makan dan minuman, favorit anak kos sekitar kampus.',
            products: [
                ['name' => 'Nasi Goreng Spesial', 'description' => 'Nasi goreng dengan telur, ayam suwir, dan acar timun.', 'price' => 22_000, 'stock' => 15, 'category' => 'makanan-berat'],
                ['name' => 'Es Teh Manis', 'description' => 'Teh manis dingin, cocok untuk menemani makan siang.', 'price' => 5_000, 'stock' => 40, 'category' => 'teh'],
                ['name' => 'Snack Box Rapat', 'description' => 'Paket snack untuk rapat atau acara organisasi kampus.', 'price' => 15_000, 'stock' => 30, 'category' => 'cemilan'],
            ],
        );

        // Additional sellers — each gets 5-8 products across different categories.
        // Skip if store already exists (idempotent — called after seller1/multi1 above).
        $this->seedStore(
            username: 'seller2',
            storeName: 'Kedai Kopi Nusantara',
            description: 'Berbagai varian kopi dari seluruh Indonesia, bisa biji atau sachet.',
            products: [
                ['name' => 'Kopi Toraja 250g', 'description' => 'Biji kopi Toraja sangrai medium, cocok untuk V60 atau drip.', 'price' => 45_000, 'stock' => 20, 'category' => 'kopi'],
                ['name' => 'Kopi Robusta Gayo 100g', 'description' => 'Biji kopi Robusta Gayo, rasa pahit pekat dan body kuat.', 'price' => 22_000, 'stock' => 35, 'category' => 'kopi'],
                ['name' => 'Sachet Latte Gula Aren', 'description' => 'Sachet kopi latte siap seduh dengan rasa gula aren.', 'price' => 8_000, 'stock' => 100, 'category' => 'kopi'],
                ['name' => 'Coklat Organik 100g', 'description' => 'Coklat batangan dari kakao organik, tanpa gula tambahan.', 'price' => 18_000, 'stock' => 0, 'category' => 'cemilan'],
            ],
        );

        $this->seedStore(
            username: 'seller3',
            storeName: 'Gadget Zone',
            description: 'Aksesori HP dan gadget original untuk mahasiswa.',
            products: [
                ['name' => 'Kabel Data Type-C 1m', 'description' => 'Kabel data USB-C fast charging, mendukung 65W PD.', 'price' => 35_000, 'stock' => 50, 'category' => 'aksesori-hp'],
                ['name' => 'Case HP Silikon Premium', 'description' => 'Case silikon anti-slip, presisi cutting untuk semua port.', 'price' => 25_000, 'stock' => 40, 'category' => 'aksesori-hp'],
                ['name' => 'Tempered Glass 2-Pack', 'description' => 'Tempered glass 9H, anti-gores dan anti-fingerprint.', 'price' => 20_000, 'stock' => 80, 'category' => 'aksesori-hp'],
                ['name' => 'Earphone TWS Wireless', 'description' => 'True wireless stereo, Bluetooth 5.3, bass kuat.', 'price' => 85_000, 'stock' => 15, 'category' => 'audio'],
                ['name' => 'Powerbank 10000mAh', 'description' => 'Powerbank slim dengan dual USB output dan fast charging.', 'price' => 95_000, 'stock' => 0, 'category' => 'aksesori-hp'],
            ],
        );

        $this->seedStore(
            username: 'seller4',
            storeName: 'Dapur Mama Diah',
            description: 'Makanan rumahan segar, MasBro dan MasSis favorit.',
            products: [
                ['name' => 'Nasi box Ayam Geprek', 'description' => 'Nasi + ayam geprek + sambal + lalapan, cocok untuk acara.', 'price' => 25_000, 'stock' => 30, 'category' => 'makanan-berat'],
                ['name' => 'Mie Goreng Spesial', 'description' => 'Mie goreng dengan telur, sayur, dan topping lengkap.', 'price' => 20_000, 'stock' => 25, 'category' => 'makanan-berat'],
                ['name' => 'Paket Diet 5 Hari', 'description' => 'Paket makanan diet seminggu, konsultasi gratis.', 'price' => 150_000, 'stock' => 10, 'category' => 'makanan-berat'],
                ['name' => 'Keripik Singkong 250g', 'description' => 'Keripik singkong renyah tanpa MSG, varian original dan pedas.', 'price' => 15_000, 'stock' => 60, 'category' => 'cemilan'],
                ['name' => 'Bolu Gulung 4 Rasa', 'description' => 'Bolu gulung lembut, pilihan rasa: keju, pandan, cokelat, strawberry.', 'price' => 30_000, 'stock' => 12, 'category' => 'cemilan'],
                ['name' => 'Es Jeruk Peras', 'description' => 'Jeruk peras segar, tanpa gula tambahan.', 'price' => 8_000, 'stock' => 0, 'category' => 'jus'],
            ],
        );

        $this->seedStore(
            username: 'seller5',
            storeName: 'Style House',
            description: 'Pakaian trendy dan affordable untuk mahasiswa.',
            products: [
                ['name' => 'Kemeja Flanel Kotak-kotak', 'description' => 'Kemeja flanel lengan panjang, bahan katun nyaman.', 'price' => 89_000, 'stock' => 20, 'category' => 'pria'],
                ['name' => 'Hoodie Oversize Unisex', 'description' => 'Hoodie fleece tebal, unisex, tersedia S-XXL.', 'price' => 125_000, 'stock' => 15, 'category' => 'pria'],
                ['name' => 'Rok Mini Plisket', 'description' => 'Rok plisket mini untuk gaya kasual sehari-hari.', 'price' => 55_000, 'stock' => 25, 'category' => 'wanita'],
                ['name' => 'T-Shirt Graphic Print', 'description' => 'T-shirt cotton combed 30s dengan print original.', 'price' => 65_000, 'stock' => 40, 'category' => 'pria'],
                ['name' => 'Sling Bag Canvas', 'description' => 'Tas selempang canvas dengan banyak kompartemen.', 'price' => 75_000, 'stock' => 18, 'category' => 'wanita'],
                ['name' => 'Kaos Polo Premium', 'description' => 'Kaos polo pique cotton, cocok untuk semi-formal.', 'price' => 95_000, 'stock' => 0, 'category' => 'pria'],
            ],
        );

        $this->seedStore(
            username: 'seller6',
            storeName: 'Sembako Sejahtera',
            description: 'Sembako lengkap dengan harga grosir untuk mahasiswa dan warga.',
            products: [
                ['name' => 'Beras Premium 5kg', 'description' => 'Beras IR 64 premium, bersih dan pulen.', 'price' => 75_000, 'stock' => 30, 'category' => 'sembako'],
                ['name' => 'Minyak Goreng 2L', 'description' => 'Minyak goreng kelapa sawit, non-RBD.', 'price' => 28_000, 'stock' => 50, 'category' => 'sembako'],
                ['name' => 'Gula Pasir 1kg', 'description' => 'Gula pasir pilihan, bersih dan kering.', 'price' => 15_000, 'stock' => 60, 'category' => 'sembako'],
                ['name' => 'Telur Ayam 1 Papan (30 butir)', 'description' => 'Telur ayam ras segar, langsung dari petani.', 'price' => 42_000, 'stock' => 20, 'category' => 'sembako'],
                ['name' => 'Sabun Cuci 900ml', 'description' => 'Sabun cuci cair dengan formula pekat, 30 kali cucian.', 'price' => 18_000, 'stock' => 40, 'category' => 'perawatan'],
            ],
        );

        $this->seedStore(
            username: 'seller7',
            storeName: 'Jasa Print & Fotokopi Campus',
            description: 'Layanan print, fotokopi, dan desain untuk kebutuhan kampus.',
            products: [
                ['name' => 'Fotokopi Hitam Putih A4 (10 lembar)', 'description' => 'Fotokopi hitam putih per 10 lembar, kertas HVS 70gsm.', 'price' => 2_000, 'stock' => 999, 'category' => 'umum'],
                ['name' => 'Print Warna A4', 'description' => 'Print warna A4, kertas art paper 120gsm.', 'price' => 500, 'stock' => 999, 'category' => 'umum'],
                ['name' => 'Print Poster A3', 'description' => 'Print poster A3, kertas art paper 150gsm.', 'price' => 3_000, 'stock' => 500, 'category' => 'umum'],
                ['name' => 'Jilid Soft Cover', 'description' => 'Jilid soft cover untuk skripsi atau laporan.', 'price' => 15_000, 'stock' => 100, 'category' => 'umum'],
                ['name' => 'Scan Dokumen', 'description' => 'Scan dokumen A4 ke format PDF atau JPG.', 'price' => 1_000, 'stock' => 999, 'category' => 'umum'],
                ['name' => 'Cetak Foto 4R (3 lembar)', 'description' => 'Cetak foto ukuran 4R, kertas glossy.', 'price' => 5_000, 'stock' => 200, 'category' => 'umum'],
                ['name' => 'Desain Banner Custom', 'description' => 'Jasa desain banner atau spanduk custom.', 'price' => 50_000, 'stock' => 10, 'category' => 'umum'],
                ['name' => 'Cetak ID Card / Karyawan', 'description' => 'Cetak kartu identitas atau karyawan dengan laminasi.', 'price' => 8_000, 'stock' => 0, 'category' => 'umum'],
            ],
        );
    }

    /**
     * @param  array<int, array{name: string, description: string, price: int, stock: int, category: string}>  $products
     */
    private function seedStore(string $username, string $storeName, string $description, array $products): void
    {
        $user = User::query()->where('username', $username)->first();

        if (! $user) {
            return;
        }

        // Skip if store already exists (idempotent on re-run).
        if ($user->store) {
            return;
        }

        $store = $this->stores->createForUser($user, [
            'name' => $storeName,
            'description' => $description,
        ]);

        foreach ($products as $data) {
            $data['category_id'] = Category::query()->where('slug', $data['category'])->value('id');
            if (! $data['category_id']) {
                continue;
            }
            $product = $this->products->createForStore($store, $data, null);
            $product->update(['image_path' => $this->generatePlaceholderImage()]);
        }
    }

    /**
     * Generate a small solid-color JPEG on the `public` disk so seeded
     * products render an image in the catalog without committing binary
     * asset files to the repo.
     */
    private function generatePlaceholderImage(): string
    {
        $image = imagecreatetruecolor(800, 600);
        $color = imagecolorallocate(
            $image,
            random_int(40, 200),
            random_int(40, 200),
            random_int(40, 200),
        );
        imagefilledrectangle($image, 0, 0, 800, 600, $color);

        ob_start();
        imagejpeg($image, null, 85);
        $contents = ob_get_clean();
        imagedestroy($image);

        $path = 'products/'.Str::random(24).'.jpg';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
