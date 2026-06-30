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
    storeName: 'Cantik Store',
    description: 'Kosmetik & Makeup',
    products: [
        [
            'name' => 'ESQA Bitty Balm Stick Blush',
            'description' => 'Blush on stik dari ESQA dengan pigmentasi yang intens dan formula yang mudah diblend.

Memberikan hasil akhir natural dan bercahaya sepanjang hari.',
            'price' => 69700,
            'stock' => 10,
            'category' => 'makeup-blush',
            'seed_images' => [
                ['source' => 'images/product/makeup/blushon/ESQA Bitty Balm Stick Blush 69,700k.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Maybelline Instant Age Rewind Eraser',
            'description' => 'Concealer ikonik dari Maybelline, berfungsi untuk menyamarkan lingkaran hitam dan garis halus.

Memiliki aplikator cushion yang mempermudah pengaplikasian.',
            'price' => 100215,
            'stock' => 15,
            'category' => 'makeup-concealer',
            'seed_images' => [
                ['source' => 'images/product/makeup/concealer/MAYBELLINE Instant Age Rewind Eraser    rp100, 215.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Skintific Cover All Perfect Air Cushion',
            'description' => 'Cushion full coverage dari Skintific. Menutupi noda hitam dan bekas jerawat dengan sempurna.

Hasil akhir matte dan tahan lama, cocok untuk kulit berminyak.',
            'price' => 128900,
            'stock' => 20,
            'category' => 'makeup-cushion',
            'seed_images' => [
                ['source' => 'images/product/makeup/cushion/SKINTIFIC Cover All Perfect Air Cushion   128,900k.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Barenbliss Lily Makes Luminous Glow Tint',
            'description' => 'Liptint dengan finish glowing natural dari Barenbliss.

Memberikan warna yang cerah dan melembapkan bibir Anda sepanjang hari.',
            'price' => 65400,
            'stock' => 30,
            'category' => 'makeup-lip',
            'seed_images' => [
                ['source' => 'images/product/makeup/glowtint/BARENBLISS Lily Makes Luminous Glow Tint  65,4K.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Make Over Silky Smooth Translucent Powder',
            'description' => 'Bedak tabur translucent dari Make Over. Mengontrol minyak berlebih dan menyamarkan pori-pori.

Hasil akhir matte dan halus, tahan lama tanpa membuat kulit kering.',
            'price' => 131700,
            'stock' => 25,
            'category' => 'makeup-powder',
            'seed_images' => [
                ['source' => 'images/product/makeup/powder/MAKE OVER Silky Smooth Translucent Powder  rp131,700.jpeg']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller2@seapedia.test',
    storeName: 'Glam Beauty',
    description: 'Produk Kecantikan Original',
    products: [
        [
            'name' => 'Maybelline Instant Age Rewind Eraser',
            'description' => 'Concealer ikonik dari Maybelline dengan packaging original.

Cocok untuk mencerahkan area bawah mata dan menutupi ketidaksempurnaan wajah.',
            'price' => 115000,
            'stock' => 12,
            'category' => 'makeup-concealer',
            'seed_images' => [
                ['source' => 'images/product/makeup/concealer/MAYBELLINE Instant Age Rewind Eraser Concealer  100, 215k.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Skintific Cover All Perfect Air Cushion',
            'description' => 'Skintific Cover All Perfect Air Cushion.

Cushion dengan coverage tinggi dan tahan lama. Praktis digunakan sehari-hari untuk hasil yang flawless.',
            'price' => 135000,
            'stock' => 18,
            'category' => 'makeup-cushion',
            'seed_images' => [
                ['source' => 'images/product/makeup/cushion/SKINTIFIC Cover All Perfect Air Cushion   rp128,900.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Make Over Silky Smooth Translucent Powder',
            'description' => 'Make Over Silky Smooth Translucent Powder.

Memberikan hasil akhir matte yang halus. Sempurna untuk mengeset makeup agar lebih tahan lama.',
            'price' => 136700,
            'stock' => 22,
            'category' => 'makeup-powder',
            'seed_images' => [
                ['source' => 'images/product/makeup/powder/MAKE OVER Silky Smooth Translucent Powder  136,700k.jpeg']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller3@seapedia.test',
    storeName: 'Sepeda Nusantara',
    description: 'Toko Sepeda dan Aksesoris',
    products: [
        [
            'name' => 'Merida Road Bike Reacto 5000',
            'description' => 'Sepeda balap Merida Reacto 5000. Didesain untuk aerodinamika maksimal dan kecepatan tinggi.

Material frame carbon ringan, cocok untuk kompetisi dan latihan jarak jauh.',
            'price' => 27499500,
            'stock' => 2,
            'category' => 'sepeda-road',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/Merida Sepeda Road Bike Reacto 5000 Ult RP 27,499,500.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Polygon Bend R2 Gravel Urban',
            'description' => 'Sepeda gravel Polygon Bend R2. Nyaman digunakan di jalan raya maupun medan semi-offroad.

Cocok untuk touring, commuting, dan petualangan di berbagai medan.',
            'price' => 5500000,
            'stock' => 5,
            'category' => 'sepeda-urban',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/SEPEDA POLYGON BEND R2 GRAVEL URBAN - S RP 5,500,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Sepeda Thrill Oust 1.0 27.5"',
            'description' => 'Sepeda gunung Thrill Oust 1.0 ukuran 27.5 inci. Sangat tangguh untuk melibas medan off-road sedang.

Tersedia dalam beberapa varian warna yang menarik.',
            'price' => 4449000,
            'stock' => 8,
            'category' => 'sepeda-mtb',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/Sepeda Dewasa - Sepeda Thrill Oust 1.0 Ukuran 27.5 - Biru RP 4,449,000.jpeg', 'variant' => 'Biru'],
                ['source' => 'images/product/Sepeda/Sepeda Dewasa - Sepeda Thrill Oust 1.0 Ukuran 27.5 - Hitam RP 4.449,000.jpeg', 'variant' => 'Hitam'],
            ],
            'has_variants' => true,
            'variants' => [
                ['name' => 'Biru', 'price' => 4449000, 'stock' => 4],
                ['name' => 'Hitam', 'price' => 4449000, 'stock' => 4],
            ],
        ],
        [
            'name' => 'Polygon Siskiu N5',
            'description' => 'Sepeda gunung Polygon Siskiu N5.

Memiliki suspensi yang empuk, cocok untuk menghadapi jalur trail dan enduro dengan percaya diri.',
            'price' => 3750000,
            'stock' => 4,
            'category' => 'sepeda-mtb',
            'seed_images' => [
                ['source' => 'images/product/Sepeda/polygon indonesia bisa RP 3.750.000.png']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller4@seapedia.test',
    storeName: 'TechHub Store',
    description: 'Elektronik dan Gadget Terkini',
    products: [
        [
            'name' => 'Panasonic Lumix FZ80D',
            'description' => 'Kamera digital Panasonic Lumix FZ80D dengan zoom optik tinggi.

Sangat ideal untuk fotografi satwa liar, olahraga, dan traveling berkat lensa yang fleksibel.',
            'price' => 6999000,
            'stock' => 3,
            'category' => 'kamera',
            'seed_images' => [
                ['source' => 'images/product/Camera/Panasonic Lumix FZ80D Digital Camera Rp 6,999,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'SteelSeries Apex Pro TKL Wireless Gen 3',
            'description' => 'Keyboard gaming wireless tenkeyless dari SteelSeries.

Dilengkapi dengan switch OmniPoint 2.0 yang dapat disesuaikan tingkat aktuasinya untuk performa maksimal.',
            'price' => 3299000,
            'stock' => 6,
            'category' => 'keyboard',
            'seed_images' => [
                ['source' => 'images/product/keyboard/Apex Pro TKL Wireless Gen 3 rp 3,299,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'iPhone 17 (Black)',
            'description' => 'Smartphone terbaru dari Apple dengan chipset tercanggih.

Desain elegan dengan layar Super Retina XDR yang cerah dan sistem kamera mutakhir.',
            'price' => 17000000,
            'stock' => 10,
            'category' => 'smartphone',
            'seed_images' => [
                ['source' => 'images/product/ip17/ipon17blackutama.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'iPhone 17 (White)',
            'description' => 'Apple iPhone 17 dengan varian warna White.

Memberikan kesan mewah dan bersih, dengan performa tinggi yang tak tertandingi.',
            'price' => 17499000,
            'stock' => 8,
            'category' => 'smartphone',
            'seed_images' => [
                ['source' => 'images/product/ip17/ipon17whiteutama.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller5@seapedia.test',
    storeName: 'iGadget Center',
    description: 'Pusat Smartphone & Aksesoris',
    products: [
        [
            'name' => 'iPhone 17 Pro Max',
            'description' => 'Seri flagship dari Apple iPhone 17 Pro Max.

Layar besar yang imersif, material titanium premium, dan daya tahan baterai terbaik.',
            'price' => 25000000,
            'stock' => 15,
            'category' => 'smartphone',
            'seed_images' => [
                ['source' => 'images/product/ipon17promaxblue.webp', 'variant' => 'Blue'],
                ['source' => 'images/product/ipon17promaxwhite.webp', 'variant' => 'White'],
                ['source' => 'images/product/ipon17promaxorange.webp', 'variant' => 'Orange'],
            ],
            'has_variants' => true,
            'variants' => [
                ['name' => 'Blue', 'price' => 25000000, 'stock' => 5],
                ['name' => 'White', 'price' => 25000000, 'stock' => 5],
                ['name' => 'Orange', 'price' => 25000000, 'stock' => 5],
            ],
        ],
        [
            'name' => 'Gantungan HP Inisial Liontin',
            'description' => 'Aksesoris gantungan HP lucu dengan inisial liontin.

Bisa juga digunakan sebagai gantungan kunci atau flashdisk.',
            'price' => 4500,
            'stock' => 100,
            'category' => 'aksesoris-hp',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/GANTUNGAN HP HANDPHONE INISIAL LIONTIN AKSESORIS HP GANTUNGAN KUNCI GANTUNGAN FLASHDISK RP 4,500.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Gantungan Tali HP Motif Branded Lanyard',
            'description' => 'Lanyard tali HP motif branded universal.

Sangat berguna agar HP tidak mudah jatuh dan praktis dibawa kemana-mana.',
            'price' => 6110,
            'stock' => 80,
            'category' => 'aksesoris-hp',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/GANTUNGAN TALI HP MOTIF BRANDED UNIVERSAL TALI HP LANYARD HANDPHONE RP 6,110.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Gantungan HP Aesthetic Girl Lonceng',
            'description' => 'Gantungan HP, tas, atau kunci dengan lonceng yang aesthetic.

Sangat cocok untuk kado atau pemakaian pribadi yang menggemaskan.',
            'price' => 7999,
            'stock' => 60,
            'category' => 'aksesoris-hp',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/Gantungan Hp, tas, kunci Lucu Aesthetic seri girl lonceng RP 7,999.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Holder HP Motor Universal GUB',
            'description' => 'Bracket holder HP GPS GUB untuk motor.

Kuat dan aman untuk dipasang pada stang motor, cocok untuk perjalanan jauh dan kurir.',
            'price' => 68310,
            'stock' => 40,
            'category' => 'aksesoris-hp',
            'seed_images' => [
                ['source' => 'images/product/aksesoris hp/Paket Breket Holder HP GPS GUB P30 P10 G81 Holder HP Motor Adv Pcx Fazzio Vario Beat Scoopy Rp 68,310.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller6@seapedia.test',
    storeName: 'Toko Sembako',
    description: 'Sembako dan Kebutuhan Harian',
    products: [
        [
            'name' => 'Sania Beras Premium 5 Kg',
            'description' => 'Beras Sania Premium kualitas terbaik. Butiran beras utuh, putih bersih, dan pulen saat dimasak.

Cocok untuk konsumsi harian keluarga Anda.',
            'price' => 85000,
            'stock' => 50,
            'category' => 'beras',
            'seed_images' => [
                ['source' => 'images/product/Beras/Sania Beras Premium 5 Kg Rp 85,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Beras BMW Pandan Wangi 5 KG',
            'description' => 'Beras BMW Pandan Wangi. Harum alami dan pulen, sangat menggugah selera makan.

Bebas pemutih dan pengawet, aman dikonsumsi.',
            'price' => 62500,
            'stock' => 45,
            'category' => 'beras',
            'seed_images' => [
                ['source' => 'images/product/Beras/beras bmw 5liter dan 5KG beras murah beras pandan wangi original bmw Rp 62,500.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Beras Sunrise 10 KG',
            'description' => 'Beras Sunrise ukuran besar 10 KG. Lebih hemat untuk keluarga besar.

Beras putih bersih, pulen, dan cocok untuk berbagai olahan nasi.',
            'price' => 120000,
            'stock' => 30,
            'category' => 'beras',
            'seed_images' => [
                ['source' => 'images/product/Beras/beras sunrise 10kg rp 120,000.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Indomie Mix Rasa 1 Dus (40 pcs)',
            'description' => 'Indomie campur rasa dalam 1 kardus isi 40 pcs.

Pilihan praktis untuk Anda yang suka berbagai rasa dari Indomie kesayangan.',
            'price' => 150000,
            'stock' => 20,
            'category' => 'mie-instan',
            'seed_images' => [
                ['source' => 'images/product/Makanan/indomie-1-dus-isi-40-bisa-mix-rasa-varian-bebas.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Indomie Goreng 1 Karton 40 pcs',
            'description' => 'Mie Goreng legendaris dari Indomie. 1 kardus isi 40 bungkus.

Rasa original yang tak tertandingi dan disukai seluruh masyarakat.',
            'price' => 150000,
            'stock' => 25,
            'category' => 'mie-instan',
            'seed_images' => [
                ['source' => 'images/product/Makanan/indomie-goreng-1karton-isi40pc-rp150,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Indomie Soto Mie 1 Kardus 40 pcs',
            'description' => 'Indomie kuah rasa Soto Mie, sangat nikmat disantap saat hujan.

1 kardus isi 40 bungkus untuk persediaan harian.',
            'price' => 146000,
            'stock' => 18,
            'category' => 'mie-instan',
            'seed_images' => [
                ['source' => 'images/product/Makanan/indomie-rasa-soto-mie-1-kardus-isi-40-pcs-rp146,000.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Ransum TNI Set Darurat',
            'description' => 'Paket Ransum TNI Darurat. Praktis dan memiliki nilai gizi tinggi.

Cocok untuk kegiatan survival, hiking, atau persediaan dalam keadaan darurat.',
            'price' => 58500,
            'stock' => 40,
            'category' => 'makanan-berat',
            'seed_images' => [
                ['source' => 'images/product/Makanan/ransumtni.jpeg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Kopi Luwak White Koffie 9 Sachet',
            'description' => 'Kopi Luwak White Koffie, nikmat dan ramah di lambung.

1 renceng isi 9 sachet, siap diseduh kapan saja.',
            'price' => 20210,
            'stock' => 60,
            'category' => 'kopi',
            'seed_images' => [
                ['source' => 'images/product/Minuman/KOPI LUWAK WHITE KOFFIE 9sX19 GR Rp 20,210.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Kopi Arabika Gayo Super Premium 200g',
            'description' => 'Biji/Bubuk Kopi Arabika Gayo Super Premium dari AZA Coffee and Roastery.

Aroma kuat dan cita rasa khas Aceh Gayo yang otentik.',
            'price' => 81960,
            'stock' => 35,
            'category' => 'kopi',
            'seed_images' => [
                ['source' => 'images/product/Minuman/Kopi Arabika Gayo Super Premium Grade 200g-Biji, Bubuk-AZA Coffee and Roastery Rp 81,960.webp']
            ],
            'has_variants' => true,
            'variants' => [
                ['name' => 'Biji Kopi', 'price' => 81960, 'stock' => 20],
                ['name' => 'Bubuk Kopi', 'price' => 81960, 'stock' => 15],
            ],
        ],
        [
            'name' => 'Kopi Tubruk Gadjah Asli 138 Gr',
            'description' => 'Kopi Tubruk Gadjah. Cita rasa kopi hitam pekat khas warung kopi Nusantara.

Kemasan ekonomis yang mudah disajikan.',
            'price' => 18700,
            'stock' => 55,
            'category' => 'kopi',
            'seed_images' => [
                ['source' => 'images/product/Minuman/Kopi Tubruk Gadjah Asli 138 Gr Rp 18,700.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Nescafé Classic Bag 90g',
            'description' => 'Nescafe Classic Instan 90 gram. Kopi hitam murni tanpa ampas.

Mudah larut bahkan dengan air dingin.',
            'price' => 51000,
            'stock' => 45,
            'category' => 'kopi',
            'seed_images' => [
                ['source' => 'images/product/Minuman/Nescafe Kopi Instan Classic Bag 90g 1pc Rp 51,000.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'seller7@seapedia.test',
    storeName: 'Toko Serba Ada',
    description: 'Perkakas dan Outdoor',
    products: [
        [
            'name' => 'Deli Household Tool Set 112 Pcs',
            'description' => 'Set perkakas rumah tangga lengkap dari Deli, terdiri dari 112 buah alat multifungsi.

Semua yang Anda butuhkan untuk perbaikan di rumah tersedia dalam satu koper praktis.',
            'price' => 1805000,
            'stock' => 10,
            'category' => 'perkakas',
            'seed_images' => [
                ['source' => 'images/product/Perkakas/Deli Household Tool Kits, Set Perkakas Rumah 112 Pcs Multifungsi Berkualitas Tinggi Dl5965 Rp 1,805,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Tekiro Mechanic Tools Set SC-MT0626',
            'description' => 'Set alat mekanik dari Tekiro SC-MT0626. Kualitas industri yang tangguh.

Sangat ideal untuk bengkel maupun mekanik rumahan.',
            'price' => 1050000,
            'stock' => 8,
            'category' => 'perkakas',
            'seed_images' => [
                ['source' => 'images/product/Perkakas/TEKIRO Mechanic Tools Set SC-MT0626 1set Rp 1,050,000 (2).webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Tactix Set 26 Pcs Perkakas Rumah Tangga',
            'description' => 'Set alat pertukangan Tactix 26 Pcs.

Ringkas, praktis, dan esensial untuk kebutuhan perbaikan ringan.',
            'price' => 723900,
            'stock' => 12,
            'category' => 'perkakas',
            'seed_images' => [
                ['source' => 'images/product/Perkakas/Tactix Set 26 Pcs Perkakas Rumah Tangga Rp 723,900.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Tenda Camping Dome Waterproof',
            'description' => 'Tenda camping model dome yang anti air (waterproof).

Kapasitas 3-4 orang, mudah dirakit dan kuat menahan angin kencang.',
            'price' => 250000,
            'stock' => 25,
            'category' => 'outdoor',
            'seed_images' => [
                ['source' => 'images/product/Tenda.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Kantong Plastik Vakum Pakaian',
            'description' => 'Kantong plastik vakum untuk mengompres pakaian.

Sangat berguna untuk menghemat ruang koper saat traveling atau penyimpanan di lemari.',
            'price' => 15000,
            'stock' => 100,
            'category' => 'rumah-tangga',
            'seed_images' => [
                ['source' => 'images/product/kantongplastikvakum.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Pelapis Kabel Insulasi Tahan Panas',
            'description' => 'Pelapis kabel insulasi yang aman dan tahan panas ekstrim.

Mencegah korsleting listrik dan menjaga keamanan instalasi kabel Anda.',
            'price' => 25000,
            'stock' => 80,
            'category' => 'perkakas',
            'seed_images' => [
                ['source' => 'images/product/pelapiskabelinsulasi.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Reel Pancing Spinning Premium',
            'description' => 'Reel pancing model spinning berkualitas premium.

Putaran halus, drag kuat, sangat handal untuk menaklukkan ikan besar.',
            'price' => 120000,
            'stock' => 30,
            'category' => 'olahraga',
            'seed_images' => [
                ['source' => 'images/product/rellpancing.jpg']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Action Figure Iron Man Mark 85 4 inci',
            'description' => 'Action figure Iron Man Mark 85 skala 4 inci dari Top Gear ZD.

Detail rapi dan cat metalik yang memukau. Wajib dimiliki para kolektor Marvel.',
            'price' => 59900,
            'stock' => 45,
            'category' => 'action-figure',
            'seed_images' => [
                ['source' => 'images/product/Action Figure/Top Gear ZD 4 inci Infinite Action Figure S Iron Man Mark 85 rp 59,900.webp']
            ],
            'has_variants' => false,
        ],
    ]
);

$this->seedStore(
    email: 'multi1@seapedia.test',
    storeName: 'Fashion Hype',
    description: 'Pakaian Hits Kekinian',
    products: [
        [
            'name' => 'Kaos Viral Gaji Bercanda Kerja Serius',
            'description' => 'Kaos plesetan viral yang nyaman dipakai sehari-hari.

Bahan katun adem, menyerap keringat, cocok untuk bersantai maupun nongkrong bareng teman.',
            'price' => 99000,
            'stock' => 40,
            'category' => 'kaos',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/Baju Parodi Gaji Kerja Serius Nyaman dan Adem - Kaos Viral Gaji Bercanda Kerja Serius Lucu Bisa Request Warna Baju Unisex Bahan Katun - Model 1, XS.jpeg']
            ],
            'has_variants' => true,
            'variants' => [
                ['name' => 'S', 'price' => 99000, 'stock' => 10],
                ['name' => 'M', 'price' => 99000, 'stock' => 10],
                ['name' => 'L', 'price' => 99000, 'stock' => 10],
                ['name' => 'XL', 'price' => 99000, 'stock' => 10],
            ],
        ],
        [
            'name' => 'Gerald Baju Kaos Fire',
            'description' => 'Baju kaos atasan pria Gerald model Fire.

Desain kasual dan stylish, pas untuk OOTD harianmu.',
            'price' => 48000,
            'stock' => 30,
            'category' => 'kaos',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/GERALD-bajufasihonatasanpakaiankaosfire-rp48,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'Jagata Coco Top Wanita Cream',
            'description' => 'Atasan wanita Coco Top dari Jagata warna cream.

Terlihat chic dan anggun. Cocok dipadukan dengan berbagai celana maupun rok panjang.',
            'price' => 189000,
            'stock' => 25,
            'category' => 'pakaian-wanita',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/Jagata Baju Atasan Wanita Kekinian Coco Top – Cream 104004 Rp 189,000.webp']
            ],
            'has_variants' => false,
        ],
        [
            'name' => 'T-Shirt Smile Love Crop Top',
            'description' => 'Crop top kekinian model serut bertuliskan Smile Love.

Tampil manis dan trendy untuk hangout akhir pekan.',
            'price' => 39450,
            'stock' => 45,
            'category' => 'pakaian-wanita',
            'seed_images' => [
                ['source' => 'images/product/Pakaian/thisrt-smile-love-baju-serut-wanita-crop-top-Rp39,450.jpeg']
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
