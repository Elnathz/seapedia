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
            email: 'seller1@seapedia.test',
            storeName: 'Toko Berkah',
            description: 'Toko kelontong dan jasa cetak di lingkungan kampus.',
            products: [
                ['name' => 'Kopi Susu Gula Aren', 'description' => 'Kopi susu segar dengan gula aren asli, diseduh setiap pagi.', 'price' => 18_000, 'stock' => 24, 'category' => 'kopi'],
                ['name' => 'Fotokopi & Print Dokumen', 'description' => 'Layanan fotokopi dan print per halaman, hitam-putih maupun warna.', 'price' => 500, 'stock' => 999, 'category' => 'umum'],
                ['name' => 'Stiker Custom', 'description' => 'Stiker vinyl custom sesuai desain pesanan, tahan air.', 'price' => 10_000, 'stock' => 60, 'category' => 'umum'],
            ],
        );

        $this->seedStore(
            email: 'multi1@seapedia.test',
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
            email: 'seller2@seapedia.test',
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
            email: 'seller3@seapedia.test',
            storeName: 'Gadget Zone',
            description: 'Gadget original dan bergaransi resmi untuk semua kalangan.',
            products: [
                [
                    'name' => 'Apple iPhone 17',
                    'description' => "Apple iPhone 17 hadir dengan desain menawan dan material premium yang tangguh. Dilengkapi layar Super Retina XDR OLED yang sangat cerah, ponsel ini menawarkan pengalaman visual yang luar biasa, baik saat Anda menonton video resolusi tinggi maupun bermain game grafis berat.\n\nDitenagai oleh chip A18 Bionic terbaru, iPhone 17 menjamin performa ngebut tanpa hambatan dan efisiensi daya yang membuat baterainya mampu bertahan seharian penuh. Kemampuan multitasking menjadi sangat mulus tanpa adanya lag sedikitpun, menjadikannya andalan untuk produktivitas harian.\n\nSistem kamera ganda canggih di bagian belakang dilengkapi sensor baru yang menangkap lebih banyak cahaya, menghasilkan foto yang tajam dan jernih bahkan di kondisi minim cahaya. Dengan fitur video Cinematic mode yang ditingkatkan, Anda bisa membuat konten layaknya profesional dengan sangat mudah.",
                    'price' => 15_999_000,
                    'stock' => 50,
                    'category' => 'handphone',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Black', 'price' => 15_999_000, 'stock' => 20],
                        ['name' => 'White', 'price' => 15_999_000, 'stock' => 30],
                    ],
                    'seed_images' => [
                        ['source' => 'images/product/ip17/iphone-17-all.webp', 'variant' => null],
                        ['source' => 'images/product/ip17/iphone-17-umum-screen.webp', 'variant' => null],
                        ['source' => 'images/product/ip17/black/iphone-17-black-depan.webp', 'variant' => 'Black'],
                        ['source' => 'images/product/ip17/black/iphone-17-black-kamera.webp', 'variant' => 'Black'],
                        ['source' => 'images/product/ip17/black/iphone-17-black-samping.webp', 'variant' => 'Black'],
                        ['source' => 'images/product/ip17/white/iphone-17-white-depan.webp', 'variant' => 'White'],
                        ['source' => 'images/product/ip17/white/iphone-17-white-camera.webp', 'variant' => 'White'],
                        ['source' => 'images/product/ip17/white/iphone-17-white-sampingwebp.webp', 'variant' => 'White'],
                    ]
                ],
                [
                    'name' => 'Apple iPhone 17 Pro Max',
                    'description' => "iPhone 17 Pro Max adalah lompatan terbesar Apple dalam teknologi smartphone, dirancang dengan bodi titanium grade aerospace yang sangat kuat namun sangat ringan. Layar ProMotion 120Hz memberikan respons sentuhan yang luar biasa mulus, memberikan kenyamanan maksimal untuk scrolling maupun gaming.\n\nDi sektor performa, chip A18 Pro menetapkan standar baru di industri dengan kemampuan ray tracing hardware-accelerated. Ini memungkinkan grafis game AAA berjalan mulus secara native di perangkat genggam Anda. Sistem pendingin internal yang baru juga memastikan perangkat tetap dingin selama penggunaan intensif.\n\nKamera Pro Max membawa fotografi ke level berikutnya dengan sensor utama 48MP yang dilengkapi kemampuan zoom optik 5x. Fitur ProRAW dan ProRes video recording memberikan kebebasan bagi para kreator untuk mengedit karya mereka dengan fleksibilitas tinggi. Baterainya yang jumbo memastikan Anda tidak perlu khawatir mencari colokan seharian penuh.",
                    'price' => 24_999_000,
                    'stock' => 45,
                    'category' => 'handphone',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Blue Titanium', 'price' => 24_999_000, 'stock' => 15],
                        ['name' => 'White Titanium', 'price' => 24_999_000, 'stock' => 15],
                        ['name' => 'Orange Titanium', 'price' => 25_499_000, 'stock' => 15],
                    ],
                    'seed_images' => [
                        // Asumsi ipon17promaxxxx.webp di public/images/product adalah gambar per-varian
                        ['source' => 'images/product/ipon17promaxblue.webp', 'variant' => 'Blue Titanium'],
                        ['source' => 'images/product/ipon17promaxwhite.webp', 'variant' => 'White Titanium'],
                        ['source' => 'images/product/ipon17promaxorange.webp', 'variant' => 'Orange Titanium'],
                    ]
                ],
            ],
        );

        $this->seedStore(
            email: 'seller4@seapedia.test',
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
            email: 'seller5@seapedia.test',
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
            email: 'seller6@seapedia.test',
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
            email: 'seller7@seapedia.test',
            storeName: 'Toko Serba Ada Campus',
            description: 'Menyediakan berbagai kebutuhan hobi, perkakas, perlengkapan outdoor, dan kebutuhan sehari-hari lainnya.',
            products: [
                [
                    'name' => 'Tenda Camping Dome Waterproof',
                    'description' => "Tenda Camping Dome ini dirancang khusus untuk petualang sejati maupun keluarga yang ingin menikmati akhir pekan di alam bebas. Dibuat dengan material polyester berkualitas tinggi yang 100% waterproof, tenda ini siap melindungi Anda dari hujan deras dan angin kencang sekalipun.\n\nDilengkapi dengan sistem sirkulasi udara ganda (dual ventilation) untuk mencegah kondensasi di dalam tenda, memastikan tidur Anda tetap nyaman dan tidak pengap. Rangka tenda menggunakan bahan fiberglass lentur namun sangat kuat, sehingga tenda dapat berdiri kokoh namun tetap ringan saat dibawa mendaki.\n\nProses perakitan sangat mudah dan cepat, hanya membutuhkan waktu kurang dari 5 menit bahkan bagi pemula. Dilengkapi juga dengan pasak besi anti-karat, tali pengikat ekstra, serta tas penyimpanan yang ringkas. Tersedia dalam dua pilihan ukuran untuk menyesuaikan kebutuhan petualangan Anda.",
                    'price' => 250_000,
                    'stock' => 15,
                    'category' => 'outdoor',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Kapasitas 2 Orang', 'price' => 250_000, 'stock' => 10],
                        ['name' => 'Kapasitas 4 Orang', 'price' => 450_000, 'stock' => 5],
                    ],
                    'seed_images' => [
                        ['source' => 'images/product/Tenda.jpg', 'variant' => null],
                    ]
                ],
                [
                    'name' => 'Rell Pancing Spinning Premium',
                    'description' => "Rell Pancing Spinning Premium ini adalah pilihan tepat bagi para pemancing laut maupun air tawar yang menginginkan performa maksimal. Dibekali dengan 12+1 ball bearing berbahan stainless steel yang sangat halus, tarikan ikan akan terasa jauh lebih ringan dan tanpa hambatan.\n\nBodi reel terbuat dari bahan graphite komposit yang ringan namun sangat tangguh menahan beban tarikan ikan monster. Spool aluminium CNC dengan desain miring (chamfered lip) meminimalisir gesekan senar saat dilempar, memungkinkan cast jarak jauh yang sangat presisi dan akurat.\n\nSistem drag menggunakan cakram karbon (carbon drag washer) yang kuat menahan beban hingga 15kg. Desain gagang (handle) ergonomis yang dapat dipindahkan ke kiri atau kanan sesuai kenyamanan Anda, dipadukan dengan kenop EVA anti-slip, memberikan kontrol penuh saat bertarung dengan tangkapan besar.",
                    'price' => 120_000,
                    'stock' => 30,
                    'category' => 'alat-pancing',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Seri 1000 (Ringan)', 'price' => 120_000, 'stock' => 15],
                        ['name' => 'Seri 2000 (Menengah)', 'price' => 135_000, 'stock' => 15],
                    ],
                    'seed_images' => [
                        ['source' => 'images/product/rellpancing.jpg', 'variant' => null],
                    ]
                ],
                [
                    'name' => 'Kantong Plastik Vakum Pakaian',
                    'description' => "Solusi cerdas untuk masalah lemari penuh! Kantong Plastik Vakum ini mampu mengompres pakaian tebal, selimut, bantal, hingga bed cover hingga menghemat ruang penyimpanan sebesar 80%. Terbuat dari bahan PA+PE premium yang ekstra tebal dan tidak mudah robek, memastikan produk Anda terlindungi dari debu, kelembapan, serangga, dan bau tidak sedap.\n\nDilengkapi dengan sistem ritsleting ganda (double zip seal) dan katup turbo anti-bocor yang inovatif, udara tidak akan bisa masuk kembali setelah disedot. Sangat cocok digunakan untuk perjalanan jauh atau pindahan rumah, karena mampu mengubah tumpukan baju tebal menjadi lembaran tipis yang mudah dimasukkan ke dalam koper.\n\nPenggunaannya sangat praktis, bisa menggunakan pompa tangan manual atau langsung disambungkan ke selang vacuum cleaner standar apa saja. Plastik ini dapat digunakan berulang kali (reusable), sehingga jauh lebih ramah lingkungan dan ekonomis.",
                    'price' => 15_000,
                    'stock' => 100,
                    'category' => 'perkakas',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Ukuran S (50x70cm)', 'price' => 15_000, 'stock' => 40],
                        ['name' => 'Ukuran M (60x80cm)', 'price' => 20_000, 'stock' => 35],
                        ['name' => 'Ukuran L (80x100cm)', 'price' => 25_000, 'stock' => 25],
                    ],
                    'seed_images' => [
                        ['source' => 'images/product/kantongplastikvakum.jpg', 'variant' => null],
                    ]
                ],
                [
                    'name' => 'Pelapis Kabel Insulasi Panas Bakar',
                    'description' => "Selongsong bakar (heat shrink tube) berkualitas tinggi ini adalah solusi profesional untuk memperbaiki insulasi kabel yang mengelupas, membungkus sambungan kabel, maupun melindungi kabel charger dari kerusakan. Terbuat dari material poliolefin (Polyolefin) yang ramah lingkungan, tidak beracun, dan bebas halogen.\n\nPelapis ini memiliki rasio penyusutan 2:1, artinya ia akan menyusut hingga setengah dari diameter aslinya saat dipanaskan, memberikan cengkeraman yang sangat kuat dan presisi pada kabel. Tahan terhadap panas tinggi, cuaca ekstrem, dan cairan bahan kimia ringan, menjadikannya pilihan ideal untuk kelistrikan otomotif maupun rumah tangga.\n\nCara pemakaiannya sangat mudah, cukup masukkan kabel ke dalam pelapis, lalu panaskan menggunakan heat gun, korek api, atau hair dryer berkekuatan tinggi selama beberapa detik hingga pelapis menyusut dan membungkus kabel dengan sempurna.",
                    'price' => 25_000,
                    'stock' => 200,
                    'category' => 'perkakas',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Warna Hitam (100 Pcs)', 'price' => 25_000, 'stock' => 100],
                        ['name' => 'Warna Merah (100 Pcs)', 'price' => 25_000, 'stock' => 100],
                    ],
                    'seed_images' => [
                        ['source' => 'images/product/pelapiskabelinsulasi.jpg', 'variant' => null],
                    ]
                ],
            ],
        );
    }

    /**
     * @param  array<int, array{name: string, description: string, price: int, stock: int, category: string}>  $products
     */
    private function seedStore(string $email, string $storeName, string $description, array $products): void
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return;
        }

        // Skip if store already exists (idempotent on re-run), but reset stock.
        if ($user->store) {
            foreach ($products as $data) {
                $product = $user->store->products()->where('name', $data['name'])->first();
                if ($product) {
                    $product->update(['stock' => $data['stock']]);
                }
            }
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

            // Extract seed_images before passing to ProductService
            $seedImages = $data['seed_images'] ?? null;
            unset($data['seed_images']);
            
            // Generate placeholder if no seed_images provided
            if (empty($seedImages) && empty($data['image_path'])) {
                $data['image_path'] = $this->generatePlaceholderImage();
            }

            $product = $this->products->createForStore($store, $data, null);
            
            if ($seedImages) {
                $this->attachImagesToProduct($product, $seedImages);
            } elseif (isset($data['image_path'])) {
                $product->update(['image_path' => $data['image_path']]);
            }
        }
    }

    private function attachImagesToProduct($product, array $imagesData): void
    {
        $maxSortOrder = -1;
        foreach ($imagesData as $index => $imgInfo) {
            $sourcePath = public_path($imgInfo['source']);
            if (file_exists($sourcePath)) {
                $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
                $destPath = 'products/' . Str::random(24) . '.' . $ext;
                Storage::disk('public')->put($destPath, file_get_contents($sourcePath));
                
                $variantId = null;
                if (isset($imgInfo['variant'])) {
                    $variant = $product->variants()->where('name', $imgInfo['variant'])->first();
                    if ($variant) {
                        $variantId = $variant->id;
                    }
                }

                $isPrimary = ($index === 0);
                
                $product->images()->create([
                    'product_variant_id' => $variantId,
                    'image_path' => $destPath,
                    'is_primary' => $isPrimary,
                    'sort_order' => $maxSortOrder + 1 + $index,
                ]);
                
                if ($isPrimary) {
                    $product->update(['image_path' => $destPath]);
                }
            }
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
