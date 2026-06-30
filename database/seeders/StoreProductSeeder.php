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
$this->seedStore(
    email: 'seller1@seapedia.test',
    storeName: 'Toko Berkah',
    description: 'Toko kelontong terpercaya',
    products: [
        [
            'name' => 'Top Gear ZD 4 inci Infinite Action Figure S Iron Man Mark 85',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 59900,
            'stock' => 14,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/Action Figure/Top Gear ZD 4 inci Infinite Action Figure S Iron Man Mark 85 rp 59,900.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'GANTUNGAN HP HANDPHONE INISIAL LIONTIN AKSESORIS HP GANTUNGAN KUNCI GANTUNGAN FLASHDISK',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 4500,
            'stock' => 71,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/GANTUNGAN HP HANDPHONE INISIAL LIONTIN AKSESORIS HP GANTUNGAN KUNCI GANTUNGAN FLASHDISK RP 4,500.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Gantungan Hp, tas, kunci Lucu Aesthetic seri girl lonceng',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 7999,
            'stock' => 61,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/Gantungan Hp, tas, kunci Lucu Aesthetic seri girl lonceng RP 7,999.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'GANTUNGAN TALI HP MOTIF BRANDED UNIVERSAL TALI HP LANYARD HANDPHONE',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 6110,
            'stock' => 75,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/GANTUNGAN TALI HP MOTIF BRANDED UNIVERSAL TALI HP LANYARD HANDPHONE RP 6,110.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Paket Breket Holder HP GPS GUB P30 P10 G81 Holder HP Motor Adv Pcx Fazzio Vario Beat Scoopy',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 68310,
            'stock' => 90,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/Paket Breket Holder HP GPS GUB P30 P10 G81 Holder HP Motor Adv Pcx Fazzio Vario Beat Scoopy Rp 68,310.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'beras bmw 5liter dan G beras murah beras pandan wangi original bmw',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 62500,
            'stock' => 77,
            'category' => 'sembako',
            'seed_images' => [
                ['source' => 'images/product/Beras/beras bmw 5liter dan 5KG beras murah beras pandan wangi original bmw Rp 62,500.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller2@seapedia.test',
    storeName: 'Elektronik Maju',
    description: 'Pusat elektronik termurah',
    products: [
        [
            'name' => 'beras sunrise g',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 120000,
            'stock' => 59,
            'category' => 'sembako',
            'seed_images' => [
                ['source' => 'images/product/Beras/beras sunrise 10kg rp 120,000.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Sania Beras Premium 5 Kg',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 85000,
            'stock' => 49,
            'category' => 'sembako',
            'seed_images' => [
                ['source' => 'images/product/Beras/Sania Beras Premium 5 Kg Rp 85,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Panasonic Lumix FZ80D Digital Camera',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 6999000,
            'stock' => 31,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/Camera/Panasonic Lumix FZ80D Digital Camera Rp 6,999,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'iPhone 17 Black',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 17000000,
            'stock' => 29,
            'category' => 'elektronik',
            'seed_images' => [
                ['source' => 'images/product/ip17/ipon17blackutama.webp']
            ],
            'has_variants' => true,
            'variants' => [
                ['name' => '128GB', 'price' => 17000000, 'stock' => 20],
                ['name' => '256GB', 'price' => 19000000, 'stock' => 10],
                ['name' => '512GB', 'price' => 23000000, 'stock' => 5],
            ],
        ],
        [
            'name' => 'iPhone 17 White',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 17499000,
            'stock' => 16,
            'category' => 'elektronik',
            'seed_images' => [
                ['source' => 'images/product/ip17/ipon17whiteutama.webp']
            ],
            'has_variants' => true,
            'variants' => [
                ['name' => '128GB', 'price' => 17499000, 'stock' => 20],
                ['name' => '256GB', 'price' => 19499000, 'stock' => 10],
                ['name' => '512GB', 'price' => 23499000, 'stock' => 5],
            ],
        ],
        [
            'name' => 'ipon17promaxblue',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 20,
            'category' => 'elektronik',
            'seed_images' => [
                ['source' => 'images/product/ipon17promaxblue.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller3@seapedia.test',
    storeName: 'Fashion Hype',
    description: 'Gaya masa kini',
    products: [
        [
            'name' => 'ipon17promaxorange',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 79,
            'category' => 'elektronik',
            'seed_images' => [
                ['source' => 'images/product/ipon17promaxorange.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'ipon17promaxwhite',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 82,
            'category' => 'elektronik',
            'seed_images' => [
                ['source' => 'images/product/ipon17promaxwhite.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'kantongplastikvakum',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 54,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/kantongplastikvakum.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Apex Pro TKL Wireless Gen 3',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 3299000,
            'stock' => 76,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/keyboard/Apex Pro TKL Wireless Gen 3 rp 3,299,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'indomie-1-dus-isi-40-bisa-mix-rasa-varian-bebas',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 91,
            'category' => 'makanan',
            'seed_images' => [
                ['source' => 'images/product/Makanan/indomie-1-dus-isi-40-bisa-mix-rasa-varian-bebas.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'indomie-goreng-arton-isi40pc',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 150000,
            'stock' => 45,
            'category' => 'makanan',
            'seed_images' => [
                ['source' => 'images/product/Makanan/indomie-goreng-1karton-isi40pc-rp150,000.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller4@seapedia.test',
    storeName: 'Gowes Sentosa',
    description: 'Toko sepeda dan aksesoris',
    products: [
        [
            'name' => 'indomie-rasa-soto-mie-1-kardus-isi-40-pcs',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 146000,
            'stock' => 17,
            'category' => 'makanan',
            'seed_images' => [
                ['source' => 'images/product/Makanan/indomie-rasa-soto-mie-1-kardus-isi-40-pcs-rp146,000.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'ransumtni',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 40,
            'category' => 'makanan',
            'seed_images' => [
                ['source' => 'images/product/Makanan/ransumtni.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'ESQA Bitty Balm Stick Blush 69,',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 700000,
            'stock' => 65,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/blushon/ESQA Bitty Balm Stick Blush 69,700k.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'MAYBELLINE Instant Age Rewind Eraser     215',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 100,
            'stock' => 10,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/concealer/MAYBELLINE Instant Age Rewind Eraser    rp100, 215.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'MAYBELLINE Instant Age Rewind Eraser Concealer  100,',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 215000,
            'stock' => 66,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/concealer/MAYBELLINE Instant Age Rewind Eraser Concealer  100, 215k.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'SKINTIFIC Cover All Perfect Air Cushion   128,',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 900000,
            'stock' => 91,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/cushion/SKINTIFIC Cover All Perfect Air Cushion   128,900k.jpeg']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller5@seapedia.test',
    storeName: 'Makeup Beauty',
    description: 'Kosmetik original',
    products: [
        [
            'name' => 'SKINTIFIC Cover All Perfect Air Cushion',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 128900,
            'stock' => 23,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/cushion/SKINTIFIC Cover All Perfect Air Cushion   rp128,900.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'BARENBLISS Lily Makes Luminous Glow Tint  65,',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 4000,
            'stock' => 40,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/glowtint/BARENBLISS Lily Makes Luminous Glow Tint  65,4K.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'MAKE OVER Silky Smooth Translucent Powder  136,',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 700000,
            'stock' => 99,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/powder/MAKE OVER Silky Smooth Translucent Powder  136,700k.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'MAKE OVER Silky Smooth Translucent Powder',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 131700,
            'stock' => 10,
            'category' => 'kosmetik',
            'seed_images' => [
                ['source' => 'images/product/makeup/powder/MAKE OVER Silky Smooth Translucent Powder  rp131,700.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Kopi Arabika Gayo Super Premium Grade 200g-Biji, Bubuk-AZA Coffee and Roastery',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 81960,
            'stock' => 21,
            'category' => 'minuman',
            'seed_images' => [
                ['source' => 'images/product/Minuman/Kopi Arabika Gayo Super Premium Grade 200g-Biji, Bubuk-AZA Coffee and Roastery Rp 81,960.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'KOPI LUWAK WHITE KOFFIE 9sX19 GR',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 20210,
            'stock' => 19,
            'category' => 'minuman',
            'seed_images' => [
                ['source' => 'images/product/Minuman/KOPI LUWAK WHITE KOFFIE 9sX19 GR Rp 20,210.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller6@seapedia.test',
    storeName: 'Sembako Murah',
    description: 'Kebutuhan dapur harian',
    products: [
        [
            'name' => 'Kopi Tubruk Gadjah Asli 138 Gr',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 18700,
            'stock' => 69,
            'category' => 'minuman',
            'seed_images' => [
                ['source' => 'images/product/Minuman/Kopi Tubruk Gadjah Asli 138 Gr Rp 18,700.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Nescafe Kopi Instan Classic Bag 90g 1pc',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 51000,
            'stock' => 79,
            'category' => 'minuman',
            'seed_images' => [
                ['source' => 'images/product/Minuman/Nescafe Kopi Instan Classic Bag 90g 1pc Rp 51,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Baju Parodi Gaji Kerja Serius Nyaman dan Adem - Kaos Viral Gaji Bercanda Kerja Serius Lucu Bisa Request Warna Baju Unisex Bahan Katun - Model 1, XS',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 99,
            'category' => 'pakaian',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/Baju Parodi Gaji Kerja Serius Nyaman dan Adem - Kaos Viral Gaji Bercanda Kerja Serius Lucu Bisa Request Warna Baju Unisex Bahan Katun - Model 1, XS.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'GERALD-bajufasihonatasanpakaiankaosfire',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 48000,
            'stock' => 31,
            'category' => 'pakaian',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/GERALD-bajufasihonatasanpakaiankaosfire-rp48,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Jagata Baju Atasan Wanita Kekinian Coco Top – Cream 104004',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 189000,
            'stock' => 19,
            'category' => 'pakaian',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/Jagata Baju Atasan Wanita Kekinian Coco Top – Cream 104004 Rp 189,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'thisrt-smile-love-baju-serut-wanita-crop-top',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 39450,
            'stock' => 68,
            'category' => 'pakaian',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/thisrt-smile-love-baju-serut-wanita-crop-top-Rp39,450.jpeg']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller7@seapedia.test',
    storeName: 'Perkakas Pro',
    description: 'Alat pertukangan lengkap',
    products: [
        [
            'name' => 'pelapiskabelinsulasi',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 82,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/pelapiskabelinsulasi.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Deli Household Tool Kits, Set Perkakas Rumah 112 Pcs Multifungsi Berkualitas Tinggi Dl5965',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 1805000,
            'stock' => 43,
            'category' => 'perkakas',
            'seed_images' => [
                ['source' => 'images/product/Perkakas/Deli Household Tool Kits, Set Perkakas Rumah 112 Pcs Multifungsi Berkualitas Tinggi Dl5965 Rp 1,805,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Tactix Set 26 Pcs Perkakas Rumah Tangga',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 723900,
            'stock' => 22,
            'category' => 'perkakas',
            'seed_images' => [
                ['source' => 'images/product/Perkakas/Tactix Set 26 Pcs Perkakas Rumah Tangga Rp 723,900.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'TEKIRO Mechanic Tools Set SC-MT0626 1set',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 1050000,
            'stock' => 68,
            'category' => 'perkakas',
            'seed_images' => [
                ['source' => 'images/product/Perkakas/TEKIRO Mechanic Tools Set SC-MT0626 1set Rp 1,050,000.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'rellpancing',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 59,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/rellpancing.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Merida Sepeda Road Bike Reacto 5000 Ult',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 27499500,
            'stock' => 22,
            'category' => 'sepeda',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/Merida Sepeda Road Bike Reacto 5000 Ult RP 27,499,500.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'multi1@seapedia.test',
    storeName: 'Warung Serba Ada',
    description: 'Toko serba ada',
    products: [
        [
            'name' => 'polygon indonesia bisa',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 3750000,
            'stock' => 54,
            'category' => 'sepeda',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/polygon indonesia bisa RP 3.750.000.png']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Sepeda Dewasa - Sepeda Thrill Oust 1.0 Ukuran 27.5 - Biru',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 4449000,
            'stock' => 76,
            'category' => 'sepeda',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/Sepeda Dewasa - Sepeda Thrill Oust 1.0 Ukuran 27.5 - Biru RP 4,449,000.jpeg']
            ],
            'has_variants' => true,
            'variants' => [
                ['name' => 'Warna Biru', 'price' => 4449000, 'stock' => 10],
                ['name' => 'Warna Hitam', 'price' => 4449000, 'stock' => 10],
            ],
        ],
        [
            'name' => 'SEPEDA POLYGON BEND R2 GRAVEL URBAN - S',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 5500000,
            'stock' => 14,
            'category' => 'sepeda',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/SEPEDA POLYGON BEND R2 GRAVEL URBAN - S RP 5,500,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Tenda',
            'description' => 'Produk berkualitas premium yang siap memenuhi kebutuhan Anda. Dibuat dengan material terbaik dan standar kualitas tinggi untuk menjamin kepuasan pelanggan.

Sangat cocok untuk penggunaan sehari-hari maupun profesional. Dilengkapi dengan garansi resmi dan dukungan purna jual yang terjamin.',
            'price' => 50000,
            'stock' => 59,
            'category' => 'umum',
            'seed_images' => [
                ['source' => 'images/product/Tenda.jpg']
            ],
            'has_variants' => false,
        ],
    ]
);

    }

    /**
     * @param  array<int, array{name: string, description: string, price: int, stock: int, category: string}>
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
