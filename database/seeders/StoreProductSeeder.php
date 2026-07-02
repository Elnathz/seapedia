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
                    'description' => 'Blush on stik eksklusif dari ESQA dengan pigmentasi yang intens dan formula creamy yang sangat mudah diaplikasikan serta diratakan pada kulit wajah. Diperkaya dengan kandungan pelembap premium untuk menjaga tekstur kulit tetap halus dan terhidrasi sepanjang hari tanpa membuatnya terasa berminyak.

Produk ini memberikan hasil akhir yang natural, merona, dan bercahaya, memastikan wajahmu selalu terlihat segar alami dari pagi hingga malam hari. Kemasannya yang praktis, mungil, dan elegan menjadikannya produk wajib di dalam tas makeup Anda, sangat cocok dibawa bepergian untuk kebutuhan touch-up kilat kapan pun diperlukan.',
                    'price' => 69700,
                    'stock' => 30,
                    'category' => 'makeup-blush',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/blushon/ESQA Bitty Balm Stick Blush 69,700k.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Maybelline Instant Age Rewind Eraser',
                    'description' => 'Concealer ikonik dari Maybelline yang sangat digemari, dirancang khusus untuk menyamarkan lingkaran hitam di bawah mata, kemerahan, serta garis halus dengan coverage yang luar biasa. Formulanya ringan dan tidak mudah creasing, menjadikannya andalan untuk tampilan wajah yang mulus dan bebas noda.

Keunggulan utama concealer ini terletak pada aplikator cushion-nya yang inovatif, yang tidak hanya mempermudah pengaplikasian tetapi juga memberikan sensasi lembut pada kulit. Anda dapat menggunakannya sebagai concealer, highlighter, atau bahkan untuk contouring ringan, menjadikannya produk serbaguna untuk rutinitas makeup harian Anda.',
                    'price' => 100215,
                    'stock' => 15,
                    'category' => 'makeup-concealer',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/concealer/MAYBELLINE Instant Age Rewind Eraser    rp100, 215.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Skintific Cover All Perfect Air Cushion',
                    'description' => 'Cushion full coverage unggulan dari Skintific yang diformulasikan dengan teknologi inovatif untuk menutupi noda hitam, bekas jerawat, dan kemerahan dengan sempurna hanya dalam satu kali tepukan. Teksturnya yang ringan membuat kulit dapat bernapas, memberikan kenyamanan maksimal meskipun digunakan berlapis-lapis.

Hasil akhirnya matte yang natural dan tahan lama, diformulasikan khusus untuk mengontrol sebum sehingga sangat cocok untuk pemilik kulit berminyak atau kombinasi. Selain itu, cushion ini juga diperkaya dengan kandungan skincare aktif yang turut merawat dan melindungi skin barrier Anda dari paparan polusi dan radikal bebas.',
                    'price' => 128900,
                    'stock' => 20,
                    'category' => 'makeup-cushion',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/cushion/SKINTIFIC Cover All Perfect Air Cushion   128,900k.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Barenbliss Lily Makes Luminous Glow Tint',
                    'description' => 'Liptint revolusioner dari Barenbliss yang menawarkan hasil akhir glowing natural, menciptakan ilusi bibir yang lebih bervolume, sehat, dan merona. Teksturnya yang ringan seperti air tidak akan terasa lengket, memberikan kenyamanan luar biasa bahkan jika digunakan seharian penuh.

Diperkaya dengan perpaduan ekstrak bunga lili dan bahan pelembap alami, liptint ini memberikan hidrasi intensif, mencegah bibir pecah-pecah sekaligus mempertahankan warna yang cerah dan mempesona. Warnanya yang buildable memungkinkan Anda mengatur intensitas warna dari natural look hingga bold sesuai dengan mood dan gaya riasan Anda.',
                    'price' => 65400,
                    'stock' => 30,
                    'category' => 'makeup-lip',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/glowtint/BARENBLISS Lily Makes Luminous Glow Tint  65,4K.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Make Over Silky Smooth Translucent Powder',
                    'description' => 'Bedak tabur translucent legendaris dari Make Over yang dirancang khusus untuk mengontrol produksi minyak berlebih di wajah, menjaga riasan Anda tetap segar dan tidak mengkilap selama berjam-jam. Partikel bedaknya yang sangat halus mampu menyamarkan pori-pori dan garis halus, menciptakan efek blur yang sempurna layaknya filter kamera.

Memberikan hasil akhir matte yang lembut, transparan, dan sangat menyatu dengan warna kulit asli Anda tanpa meninggalkan white cast atau mengubah warna foundation dasar. Formulanya yang ringan dan tidak menyumbat pori memastikan kulit tetap nyaman, tidak terasa kering, dan bebas kilap sepanjang hari.',
                    'price' => 131700,
                    'stock' => 25,
                    'category' => 'makeup-powder',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/powder/MAKE OVER Silky Smooth Translucent Powder  rp131,700.jpeg'],
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
                    'description' => 'Concealer ikonik dari Maybelline yang sangat digemari, dirancang khusus untuk menyamarkan lingkaran hitam di bawah mata, kemerahan, serta garis halus dengan coverage yang luar biasa. Formulanya ringan dan tidak mudah creasing, menjadikannya andalan untuk tampilan wajah yang mulus dan bebas noda.

Keunggulan utama concealer ini terletak pada aplikator cushion-nya yang inovatif, yang tidak hanya mempermudah pengaplikasian tetapi juga memberikan sensasi lembut pada kulit. Anda dapat menggunakannya sebagai concealer, highlighter, atau bahkan untuk contouring ringan, menjadikannya produk serbaguna untuk rutinitas makeup harian Anda.',
                    'price' => 115000,
                    'stock' => 12,
                    'category' => 'makeup-concealer',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/concealer/MAYBELLINE Instant Age Rewind Eraser Concealer  100, 215k.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Skintific Cover All Perfect Air Cushion',
                    'description' => 'Cushion full coverage unggulan dari Skintific yang diformulasikan dengan teknologi inovatif untuk menutupi noda hitam, bekas jerawat, dan kemerahan dengan sempurna hanya dalam satu kali tepukan. Teksturnya yang ringan membuat kulit dapat bernapas, memberikan kenyamanan maksimal meskipun digunakan berlapis-lapis.

Hasil akhirnya matte yang natural dan tahan lama, diformulasikan khusus untuk mengontrol sebum sehingga sangat cocok untuk pemilik kulit berminyak atau kombinasi. Selain itu, cushion ini juga diperkaya dengan kandungan skincare aktif yang turut merawat dan melindungi skin barrier Anda dari paparan polusi dan radikal bebas.',
                    'price' => 135000,
                    'stock' => 18,
                    'category' => 'makeup-cushion',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/cushion/SKINTIFIC Cover All Perfect Air Cushion   rp128,900.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Make Over Silky Smooth Translucent Powder',
                    'description' => 'Bedak tabur translucent legendaris dari Make Over yang dirancang khusus untuk mengontrol produksi minyak berlebih di wajah, menjaga riasan Anda tetap segar dan tidak mengkilap selama berjam-jam. Partikel bedaknya yang sangat halus mampu menyamarkan pori-pori dan garis halus, menciptakan efek blur yang sempurna layaknya filter kamera.

Memberikan hasil akhir matte yang lembut, transparan, dan sangat menyatu dengan warna kulit asli Anda tanpa meninggalkan white cast atau mengubah warna foundation dasar. Formulanya yang ringan dan tidak menyumbat pori memastikan kulit tetap nyaman, tidak terasa kering, dan bebas kilap sepanjang hari.',
                    'price' => 136700,
                    'stock' => 22,
                    'category' => 'makeup-powder',
                    'seed_images' => [
                        ['source' => 'images/product/makeup/powder/MAKE OVER Silky Smooth Translucent Powder  136,700k.jpeg'],
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
                    'description' => 'Sepeda balap kelas atas Merida Reacto 5000 yang diciptakan khusus untuk para pecinta kecepatan dan performa maksimal di jalan raya. Didesain dengan geometri aerodinamis yang canggih, sepeda ini mampu membelah angin dengan efisiensi tinggi, mengurangi hambatan secara signifikan sehingga Anda dapat melaju lebih kencang dengan upaya yang lebih efisien.

Dibangun menggunakan material frame full carbon yang sangat ringan namun memiliki tingkat kekakuan (stiffness) yang luar biasa, memberikan responsivitas seketika pada setiap kayuhan pedal. Sepeda ini adalah pilihan sempurna bagi Anda yang ingin mendominasi lintasan balap, kompetisi serius, maupun untuk sesi latihan jarak jauh yang menuntut performa puncak.',
                    'price' => 27499500,
                    'stock' => 2,
                    'category' => 'sepeda-road',
                    'seed_images' => [
                        ['source' => 'images/product/Sepeda/Merida Sepeda Road Bike Reacto 5000 Ult RP 27,499,500.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Polygon Bend R2 Gravel Urban',
                    'description' => 'Sepeda gravel serbaguna Polygon Bend R2 yang dirancang untuk menaklukkan berbagai medan, mulai dari mulusnya aspal jalan raya hingga kasarnya jalur semi-offroad dan bebatuan ringan. Geometrinya difokuskan pada kenyamanan pengendara (endurance geometry), memungkinkan Anda bersepeda berjam-jam tanpa merasa kelelahan yang berlebihan di area punggung dan bahu.

Dilengkapi dengan ban tapak lebar yang menyerap getaran dan memberikan traksi maksimal di medan licin maupun berpasir. Sepeda ini sangat cocok untuk aktivitas touring jarak jauh, bike packing, commuting harian, hingga sekadar petualangan akhir pekan mengeksplorasi rute-rute baru di pinggiran kota.',
                    'price' => 5500000,
                    'stock' => 5,
                    'category' => 'sepeda-urban',
                    'seed_images' => [
                        ['source' => 'images/product/Sepeda/SEPEDA POLYGON BEND R2 GRAVEL URBAN - S RP 5,500,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Sepeda Thrill Oust 1.0 27.5"',
                    'description' => 'Sepeda gunung (MTB) Thrill Oust 1.0 dengan ukuran roda 27.5 inci yang menawarkan keseimbangan sempurna antara kelincahan bermanuver dan kemampuan melibas rintangan. Suspensinya dirancang tangguh untuk meredam guncangan di medan off-road tingkat sedang, memberikan kenyamanan maksimal saat melewati bebatuan dan jalanan bergelombang.

Frame-nya terbuat dari material alloy yang kokoh namun cukup ringan untuk bermanuver lincah di jalur trail, dipadukan dengan sistem pengereman cakram yang responsif untuk keamanan optimal di segala kondisi cuaca. Hadir dalam berbagai varian warna yang sporty dan dinamis, menjadikannya pilihan favorit bagi penggemar petualangan alam terbuka.',
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
                    'name' => 'Polygon Indonesia Bisa',
                    'description' => 'Sepeda gunung edisi spesial Polygon Indonesia Bisa yang merepresentasikan semangat juang dan kualitas produk karya anak bangsa. Dirancang dengan suspensi empuk dan travel yang mumpuni, sepeda ini sangat handal untuk menaklukkan jalur trail yang menantang, medan enduro, hingga jalanan berbatu dengan penuh percaya diri.

Material framenya yang solid dirancang untuk daya tahan jangka panjang, memastikan performa yang konsisten meskipun sering digunakan di medan ekstrem. Ergonomi stang dan sadelnya juga telah disesuaikan dengan postur tubuh pesepeda Asia, menjamin kenyamanan ekstra saat menanjak curam maupun saat turunan cepat.',
                    'price' => 3750000,
                    'stock' => 4,
                    'category' => 'sepeda-mtb',
                    'seed_images' => [
                        ['source' => 'images/product/Sepeda/polygon indonesia bisa RP 3.750.000.png'],
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
                    'description' => 'Kamera digital prosumer Panasonic Lumix FZ80D yang luar biasa, mengandalkan fitur zoom optik super tinggi yang memungkinkan Anda menangkap objek jarak jauh dengan detail yang mencengangkan. Lensa ultra-wide hingga super-telephotonya menjadikan kamera ini senjata andalan untuk fotografi satwa liar, dokumentasi olahraga, hingga mengabadikan momen berharga saat traveling.

Selain kemampuan fotonya yang brilian, kamera ini juga mendukung perekaman video beresolusi 4K yang tajam dan mulus, ideal untuk vlogging maupun pembuatan konten sinematik. Desain bodinya yang ergonomis ala DSLR memberikan grip yang mantap, sementara fitur stabilisasi gambarnya memastikan hasil foto tetap tajam meskipun Anda memotret tanpa bantuan tripod.',
                    'price' => 6999000,
                    'stock' => 3,
                    'category' => 'kamera',
                    'seed_images' => [
                        ['source' => 'images/product/Camera/Panasonic Lumix FZ80D Digital Camera Rp 6,999,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'SteelSeries Apex Pro TKL Wireless Gen 3',
                    'description' => 'Keyboard gaming wireless tenkeyless (TKL) revolusioner dari SteelSeries yang mendefinisikan ulang kecepatan dan presisi. Dilengkapi dengan switch mekanikal OmniPoint 2.0 generasi terbaru yang memungkinkan Anda menyesuaikan tingkat aktuasi pada setiap tombol secara individual, mulai dari sentuhan paling ringan untuk kecepatan maksimal hingga tekanan dalam untuk akurasi mengetik.

Konektivitas nirkabelnya yang bebas lag menjamin respons seketika tanpa gangguan kabel, menciptakan setup meja yang bersih dan minimalis. Dengan rangka aluminium aircraft-grade yang sangat kokoh, layar OLED pintar untuk notifikasi sistem, dan pencahayaan RGB per-tombol yang memukau, keyboard ini adalah investasi wajib bagi para gamer profesional dan enthusiast sejati.',
                    'price' => 3299000,
                    'stock' => 6,
                    'category' => 'keyboard',
                    'seed_images' => [
                        ['source' => 'images/product/keyboard/Apex Pro TKL Wireless Gen 3 rp 3,299,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'iPhone 17 (Black)',
                    'description' => 'Smartphone flagship terbaru dari Apple yang membawa standar inovasi ke level yang lebih tinggi. Ditenagai oleh chipset Apple generasi paling mutakhir, perangkat ini menawarkan performa komputasi luar biasa yang mampu melibas segala aplikasi berat, rendering video 4K, hingga game dengan grafis AAA tanpa hambatan sedikit pun.

Desainnya tetap mempertahankan siluet elegan khas Apple dengan material kaca dan aluminium premium yang kokoh, dibalut dalam warna Black yang klasik dan misterius. Layar Super Retina XDR-nya menampilkan warna yang sangat akurat, kontras tak terhingga, dan kecerahan puncak yang menakjubkan, memberikan pengalaman visual paling imersif baik di dalam ruangan maupun di bawah terik matahari.',
                    'price' => 17000000,
                    'stock' => 5,
                    'category' => 'smartphone',
                    'seed_images' => [
                        ['source' => 'images/product/ip17/ipon17blackutama.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'iPhone 17 (White)',
                    'description' => 'Apple iPhone 17 varian warna White yang tampil sangat bersih, minimalis, dan memancarkan aura kemewahan yang tak lekang oleh waktu. Smartphone ini tidak hanya unggul dalam segi desain, tetapi juga membawa sistem kamera ganda yang telah ditingkatkan secara signifikan untuk menghasilkan foto low-light yang jernih dan video sinematik dengan stabilisasi kelas pro.

Performa tinggi yang tak tertandingi di kelasnya didukung oleh efisiensi daya baterai yang luar biasa, memastikan Anda tetap terhubung, produktif, dan terhibur sepanjang hari penuh hanya dalam satu kali pengisian daya. Ekosistem iOS yang mulus dan fitur privasi tingkat lanjut menjadikan iPhone 17 perangkat pintar yang aman dan menyenangkan untuk digunakan sehari-hari.',
                    'price' => 17499000,
                    'stock' => 2,
                    'category' => 'smartphone',
                    'seed_images' => [
                        ['source' => 'images/product/ip17/ipon17whiteutama.webp'],
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
                    'description' => 'Seri flagship tertinggi dan paling bergengsi dari Apple, iPhone 17 Pro Max. Hadir dengan layar OLED berukuran masif yang menawarkan pengalaman visual paling imersif, mendukung refresh rate tinggi ProMotion untuk pergerakan antarmuka yang sangat mulus, responsif, dan memanjakan mata Anda.

Dibingkai dengan material titanium aerospace-grade yang tidak hanya memberikan tampilan ultra-premium, tetapi juga membuatnya lebih ringan dan jauh lebih tangguh terhadap goresan maupun benturan. Sistem kamera Pro-nya menawarkan rentang zoom optik terpanjang yang pernah ada di iPhone, kemampuan merekam video ProRes profesional, serta daya tahan baterai paling awet yang menjadikannya perangkat pamungkas bagi para profesional, kreator konten, dan tech enthusiast.',
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
                    'description' => 'Aksesoris gantungan HP super lucu dan estetis yang dilengkapi dengan inisial liontin elegan, memberikan sentuhan personalisasi unik pada ponsel kesayangan Anda. Desainnya yang manis dengan manik-manik berkualitas membuatnya tidak hanya sekadar aksesoris, tetapi juga fashion statement yang menarik perhatian.

Selain digunakan untuk mempercantik smartphone, gantungan ini sangat multifungsi dan kokoh, sehingga bisa juga Anda fungsikan sebagai gantungan kunci mobil, penghias tas punggung, maupun penanda flashdisk agar tidak mudah hilang. Cocok dijadikan hadiah berkesan untuk sahabat atau orang terkasih pada momen-momen spesial.',
                    'price' => 4500,
                    'stock' => 100,
                    'category' => 'aksesoris-hp',
                    'seed_images' => [
                        ['source' => 'images/product/aksesoris hp/GANTUNGAN HP HANDPHONE INISIAL LIONTIN AKSESORIS HP GANTUNGAN KUNCI GANTUNGAN FLASHDISK RP 4,500.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Gantungan Tali HP Motif Branded Lanyard',
                    'description' => 'Lanyard tali HP bergaya premium dengan motif branded universal yang memancarkan kesan mewah dan stylish saat dikalungkan. Material talinya ditenun dari bahan nilon berkualitas tinggi yang sangat lembut di kulit leher, tidak membuat gatal, dan tidak mudah berserabut meskipun digunakan setiap hari.

Sangat praktis dan fungsional untuk menjaga smartphone Anda agar tidak mudah jatuh atau hilang saat berada di keramaian, konser, maupun saat traveling. Dilengkapi dengan ring holder yang kuat dan pengait metal anti-karat, menjamin keamanan perangkat Anda sekaligus memberikan kemudahan akses saat ingin memotret dengan cepat.',
                    'price' => 6110,
                    'stock' => 80,
                    'category' => 'aksesoris-hp',
                    'seed_images' => [
                        ['source' => 'images/product/aksesoris hp/GANTUNGAN TALI HP MOTIF BRANDED UNIVERSAL TALI HP LANYARD HANDPHONE RP 6,110.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Gantungan HP Aesthetic Girl Lonceng',
                    'description' => 'Gantungan HP dengan desain ala Korea yang sangat aesthetic, menampilkan figur seri \'girl\' dipadukan dengan lonceng kecil yang akan bergemerincing manis setiap kali Anda bergerak. Detail pembuatannya sangat rapi dengan pemilihan warna-warna pastel yang menenangkan dan menggemaskan.

Ukurannya yang pas tidak akan membuat smartphone Anda terasa berat, namun cukup menonjol untuk memberikan aksen lucu. Sangat direkomendasikan untuk koleksi pribadi remaja putri maupun sebagai kado ulang tahun yang unik, bisa digantungkan pada pouch kosmetik, tas sekolah, hingga kunci kamar Anda.',
                    'price' => 7999,
                    'stock' => 60,
                    'category' => 'aksesoris-hp',
                    'seed_images' => [
                        ['source' => 'images/product/aksesoris hp/Gantungan Hp, tas, kunci Lucu Aesthetic seri girl lonceng RP 7,999.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Holder HP Motor Universal GUB',
                    'description' => 'Bracket holder HP GUB premium yang dirancang khusus untuk memenuhi mobilitas tinggi para pengendara motor, baik untuk kegiatan touring jarak jauh maupun para pekerja ojek online. Terbuat dari material aluminium alloy CNC yang sangat solid, anti-karat, dan tidak akan patah meskipun terkena guncangan hebat di jalanan berlubang.

Desain clamp-nya dilengkapi dengan bantalan silikon tebal yang akan mencengkeram smartphone Anda dengan sangat kuat dari empat sisi, memastikan HP tidak akan terlepas tanpa menggores bodinya. Model universal ini kompatibel dengan hampir semua ukuran smartphone modern dan dapat dipasang dengan mudah pada berbagai jenis stang motor bebek, matic, hingga sport.',
                    'price' => 68310,
                    'stock' => 40,
                    'category' => 'aksesoris-hp',
                    'seed_images' => [
                        ['source' => 'images/product/aksesoris hp/Paket Breket Holder HP GPS GUB P30 P10 G81 Holder HP Motor Adv Pcx Fazzio Vario Beat Scoopy Rp 68,310.webp'],
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
                    'description' => 'Beras Sania Premium kualitas ekspor yang diproses menggunakan teknologi modern untuk menghasilkan butiran beras utuh yang putih bersih, bebas dari kerikil maupun gabah. Aroma alaminya yang segar menandakan beras ini baru digiling dan diproses tanpa menggunakan bahan pemutih atau pengawet kimia berbahaya.

Saat dimasak, beras ini menghasilkan tekstur nasi yang sangat pulen, tidak mudah basi, dan tidak lengket berlebihan, menjadikannya kanvas sempurna untuk berbagai lauk pauk Nusantara. Kemasan 5 Kg ini adalah pilihan paling ideal untuk memenuhi kebutuhan konsumsi harian keluarga Anda, menjamin hidangan yang nikmat di setiap suapan.',
                    'price' => 85000,
                    'stock' => 50,
                    'category' => 'beras',
                    'seed_images' => [
                        ['source' => 'images/product/Beras/Sania Beras Premium 5 Kg Rp 85,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Beras BMW Pandan Wangi 5 KG',
                    'description' => 'Beras spesial BMW Pandan Wangi asli yang terkenal dengan aroma daun pandan alami yang sangat khas dan menggugah selera bahkan sejak saat beras baru mulai ditanak. Kualitas berasnya terjamin putih bersih, terbebas dari hama kutu, dan dijamin 100% tanpa menggunakan esens buatan maupun pewangi sintetis.

Tekstur nasinya yang pulen sempurna dan sedikit lengket membuatnya sangat cocok disajikan hangat-hangat bersama lauk sederhana sekalipun. Sangat aman dan sehat untuk dikonsumsi setiap hari oleh anak-anak maupun orang dewasa, memberikan pengalaman makan malam keluarga yang lebih istimewa.',
                    'price' => 62500,
                    'stock' => 45,
                    'category' => 'beras',
                    'seed_images' => [
                        ['source' => 'images/product/Beras/beras bmw 5liter dan 5KG beras murah beras pandan wangi original bmw Rp 62,500.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Beras Sunrise 10 KG',
                    'description' => 'Beras Sunrise ukuran ekonomis 10 KG yang dirancang untuk keluarga besar, bisnis katering skala kecil, maupun untuk keperluan donasi yang membutuhkan kuantitas banyak tanpa mengorbankan kualitas. Berasnya berukuran seragam, putih bersih, dan telah melewati proses penyaringan ketat untuk meminimalisir persentase beras patah (broken rice).

Nasi yang dihasilkan memiliki tingkat kepulenan sedang yang pas, tidak terlalu lembek, sehingga sangat cocok untuk diolah menjadi berbagai menu seperti nasi goreng, nasi uduk, maupun nasi kuning. Pilihan cerdas bagi Anda yang mencari beras dengan value for money terbaik untuk kebutuhan konsumsi jangka panjang.',
                    'price' => 120000,
                    'stock' => 30,
                    'category' => 'beras',
                    'seed_images' => [
                        ['source' => 'images/product/Beras/beras sunrise 10kg rp 120,000.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Indomie Mix Rasa 1 Dus (40 pcs)',
                    'description' => 'Paket komplit Indomie Mix Rasa dalam 1 kardus besar berisi 40 bungkus yang menawarkan petualangan rasa tak terbatas. Di dalamnya terdapat campuran berbagai varian rasa favorit yang dipilih secara acak, mulai dari rasa kuah yang menyegarkan hingga mie goreng yang gurih menggoda, memberikan kejutan nikmat di setiap bungkusnya.

Pilihan paling praktis bagi Anda, keluarga, atau anak kos yang mudah bosan dengan satu rasa saja. Dengan paket mix ini, Anda selalu memiliki variasi menu mie instan yang siap menemani momen begadang mengerjakan tugas, bersantai di akhir pekan, atau sebagai penyelamat lapar di tengah malam hujan.',
                    'price' => 150000,
                    'stock' => 20,
                    'category' => 'mie-instan',
                    'seed_images' => [
                        ['source' => 'images/product/Makanan/indomie-1-dus-isi-40-bisa-mix-rasa-varian-bebas.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Indomie Goreng 1 Karton 40 pcs',
                    'description' => 'Mie Goreng instan legendaris dari Indomie yang kepopulerannya telah diakui tak hanya di Indonesia, tetapi juga di kancah internasional. 1 kardus berisi 40 bungkus, menawarkan kenikmatan rasa original yang memadukan bumbu gurih, minyak bawang yang wangi, saus cabai pedas manis, dan kecap manis kental yang tak tertandingi.

Tekstur mienya yang kenyal dan pas berpadu sempurna dengan taburan bawang goreng renyah, menciptakan harmoni rasa yang selalu bikin rindu. Membeli dalam kemasan karton adalah solusi paling cerdas dan hemat untuk stok bulanan rumah tangga, bekal darurat, maupun hidangan cepat saji saat teman-teman berkunjung.',
                    'price' => 150000,
                    'stock' => 25,
                    'category' => 'mie-instan',
                    'seed_images' => [
                        ['source' => 'images/product/Makanan/indomie-goreng-1karton-isi40pc-rp150,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Indomie Soto Mie 1 Kardus 40 pcs',
                    'description' => 'Varian mie kuah terfavorit dari Indomie, rasa Soto Mie yang sangat otentik dan menyegarkan. Kemasan 1 kardus isi 40 bungkus ini siap menghangatkan suasana, dengan perpaduan kaldu soto yang kaya rempah, aroma jeruk nipis yang khas, dan tekstur mie yang lembut namun tidak mudah lembek.

Sensasi hangat dan gurihnya menjadikannya comfort food sejati, sangat nikmat disantap saat cuaca sedang hujan, cuaca dingin, atau saat Anda sedang kurang enak badan. Dilengkapi dengan serbuk koya yang gurih, semangkuk Indomie Soto Mie panas adalah jaminan mood booster yang praktis untuk persediaan harian Anda.',
                    'price' => 146000,
                    'stock' => 18,
                    'category' => 'mie-instan',
                    'seed_images' => [
                        ['source' => 'images/product/Makanan/indomie-rasa-soto-mie-1-kardus-isi-40-pcs-rp146,000.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Ransum TNI Set Darurat',
                    'description' => 'Paket Ransum Makanan Darurat standar militer (TNI) yang dirancang khusus untuk bertahan dalam kondisi paling ekstrem sekalipun. Ransum ini diproses menggunakan teknologi sterilisasi tingkat tinggi sehingga dapat disimpan dalam jangka waktu yang sangat lama tanpa memerlukan pendingin, namun tetap aman dan higienis untuk dikonsumsi.

Setiap paketnya mengandung asupan kalori dan nutrisi makro yang padat, dirancang untuk memulihkan energi dengan cepat setelah aktivitas fisik berat. Selain menjadi perlengkapan wajib bagi pecinta kegiatan outdoor, survival, dan pendakian gunung, ransum ini juga merupakan elemen krusial untuk disimpan di rumah sebagai persiapan mitigasi bencana alam atau keadaan darurat.',
                    'price' => 58500,
                    'stock' => 0,
                    'category' => 'makanan-berat',
                    'seed_images' => [
                        ['source' => 'images/product/Makanan/ransumtni.jpeg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Kopi Luwak White Koffie 9 Sachet',
                    'description' => 'Kopi instan Luwak White Koffie yang pelopor kopi putih di Indonesia, terkenal dengan cita rasanya yang sangat creamy, manis yang pas, dan aroma kopi yang menggoda tanpa meninggalkan rasa asam. Melalui proses pemanggangan khusus, kopi ini diklaim lebih ramah dan aman bagi lambung, cocok untuk Anda yang sensitif terhadap kopi hitam biasa.

Kemasan praktis 1 renceng berisi 9 sachet ini sangat mudah diseduh, baik menggunakan air panas untuk kehangatan pagi maupun disajikan dingin dengan es batu untuk menyegarkan siang hari Anda. Solusi ngopi nikmat yang ekonomis untuk menemani aktivitas bekerja atau bersantai di rumah.',
                    'price' => 20210,
                    'stock' => 60,
                    'category' => 'kopi',
                    'seed_images' => [
                        ['source' => 'images/product/Minuman/KOPI LUWAK WHITE KOFFIE 9sX19 GR Rp 20,210.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Kopi Arabika Gayo Super Premium 200g',
                    'description' => 'Kopi Arabika Gayo Super Premium persembahan AZA Coffee and Roastery, dipanen langsung dari dataran tinggi Gayo, Aceh, yang tersohor akan kualitas biji kopinya di mata dunia. Biji kopi ini diseleksi dengan sangat ketat (Grade 1) dan di-roasting oleh roaster berpengalaman untuk mengeluarkan potensi rasa terbaiknya secara maksimal.

Menawarkan profil rasa yang sangat kompleks dengan body yang tebal, aroma rempah yang eksotis, tingkat keasaman (acidity) yang rendah, serta hint rasa cokelat dan karamel yang membekas lama di lidah (long aftertaste). Tersedia dalam bentuk biji utuh maupun bubuk, kopi ini adalah mahakarya bagi para penikmat kopi sejati yang menghargai cita rasa otentik.',
                    'price' => 81960,
                    'stock' => 0,
                    'category' => 'kopi',
                    'seed_images' => [
                        ['source' => 'images/product/Minuman/Kopi Arabika Gayo Super Premium Grade 200g-Biji, Bubuk-AZA Coffee and Roastery Rp 81,960.webp'],
                    ],
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Biji Kopi', 'price' => 81960, 'stock' => 20],
                        ['name' => 'Bubuk Kopi', 'price' => 81960, 'stock' => 15],
                    ],
                ],
                [
                    'name' => 'Kopi Tubruk Gadjah Asli 138 Gr',
                    'description' => 'Kopi Tubruk Gadjah Asli yang membawa Anda kembali bernostalgia pada cita rasa kopi hitam pekat ala warung kopi tradisional Nusantara yang legendaris. Dibuat dari perpaduan biji kopi robusta pilihan yang digiling halus, menghasilkan aroma kopi tubruk yang tajam, kuat, dan langsung membangkitkan semangat sejak hirupan pertama.

Rasanya yang bold dan otentik sangat pas dinikmati tanpa gula bagi para purist, atau ditambahkan susu kental manis untuk sensasi ngopi yang lebih santai. Kemasan pouch ekonomis 138 gram ini dirancang untuk menjaga kesegaran bubuk kopi, memudahkan Anda menyajikan secangkir kopi hitam mantap kapan pun Anda inginkan.',
                    'price' => 18700,
                    'stock' => 2,
                    'category' => 'kopi',
                    'seed_images' => [
                        ['source' => 'images/product/Minuman/Kopi Tubruk Gadjah Asli 138 Gr Rp 18,700.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Nescafé Classic Bag 90g',
                    'description' => 'Nescafé Classic Instan dalam kemasan pouch 90 gram, menyajikan 100% kopi murni tanpa campuran ampas yang diekstrak secara sempurna untuk mempertahankan aroma kuat khas Nescafe. Teknologi pengolahannya memastikan bubuk kopi ini sangat mudah larut secara instan, bahkan ketika diseduh langsung menggunakan air dingin tanpa menggumpal.

Fleksibilitasnya menjadikannya bahan dasar yang sempurna untuk berbagai kreasi minuman kopi ala kafe, seperti dalgona coffee, es kopi susu gula aren, maupun sekadar secangkir kopi hitam murni untuk memulai hari. Kemasan zip-lock-nya sangat praktis untuk menjaga aroma kopi tetap kuat meskipun sudah dibuka berulang kali.',
                    'price' => 51000,
                    'stock' => 3,
                    'category' => 'kopi',
                    'seed_images' => [
                        ['source' => 'images/product/Minuman/Nescafe Kopi Instan Classic Bag 90g 1pc Rp 51,000.webp'],
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
                    'description' => 'Set perkakas rumah tangga ultra-komplit dari merek ternama Deli, menghadirkan koleksi 112 buah alat multifungsi berkualitas tinggi dalam satu genggaman. Set ini mencakup berbagai jenis tang, palu, obeng presisi, kunci inggris, meteran, hingga set mata bor dan baut, semuanya terbuat dari baja karbon kokoh yang anti-karat dan tahan lama.

Seluruh perkakas tersimpan rapi dalam sebuah koper penyimpanan (hard case) yang didesain ergonomis, memastikan setiap alat memiliki slot khususnya sendiri agar tidak berserakan dan mudah ditemukan saat dibutuhkan. Set ini adalah investasi seumur hidup yang wajib dimiliki setiap rumah untuk menangani segala jenis perbaikan, perakitan furnitur, maupun proyek DIY Anda.',
                    'price' => 1805000,
                    'stock' => 0,
                    'category' => 'perkakas',
                    'seed_images' => [
                        ['source' => 'images/product/Perkakas/Deli Household Tool Kits, Set Perkakas Rumah 112 Pcs Multifungsi Berkualitas Tinggi Dl5965 Rp 1,805,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Tekiro Mechanic Tools Set SC-MT0626',
                    'description' => 'Set alat mekanik profesional SC-MT0626 persembahan Tekiro yang reputasinya tak perlu diragukan lagi di dunia otomotif dan industri. Dibuat menggunakan material Chrome Vanadium (Cr-V) berkualitas tinggi yang terkenal akan kekuatannya yang luar biasa terhadap torsi tinggi, tidak mudah slek, dan tahan karat untuk penggunaan puluhan tahun.

Set ini berisikan kunci pas, kunci ring, soket, dan tuas rachet dengan tingkat presisi yang sangat akurat, meminimalisir risiko rusaknya kepala baut kendaraan Anda. Dikemas dalam box besi yang solid dan kokoh, set alat ini sangat ideal untuk menjadi andalan di bengkel profesional maupun untuk mekanik rumahan yang hobi mengoprek mesin.',
                    'price' => 1050000,
                    'stock' => 8,
                    'category' => 'perkakas',
                    'seed_images' => [
                        ['source' => 'images/product/Perkakas/TEKIRO Mechanic Tools Set SC-MT0626 1set Rp 1,050,000 (2).webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Tactix Set 26 Pcs Perkakas Rumah Tangga',
                    'description' => 'Set perkakas esensial 26 Pcs dari Tactix yang dirancang dengan mengedepankan kepraktisan dan mobilitas untuk kebutuhan perbaikan ringan sehari-hari. Desain setiap gagang perkakasnya sangat ergonomis, dilapisi karet anti-slip yang nyaman digenggam, sehingga meminimalisir kelelahan tangan saat digunakan untuk waktu yang lama.

Set ringkas ini berisi peralatan paling umum yang sering dibutuhkan di rumah, seperti tang potong, obeng plus minus, cutter, dan pita ukur. Sangat cocok ditempatkan di dalam laci meja kerja, di dalam bagasi mobil, atau sebagai perlengkapan pertukangan pertama bagi Anda yang baru pindah ke apartemen atau rumah baru.',
                    'price' => 723900,
                    'stock' => 12,
                    'category' => 'perkakas',
                    'seed_images' => [
                        ['source' => 'images/product/Perkakas/Tactix Set 26 Pcs Perkakas Rumah Tangga Rp 723,900.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Tenda Camping Dome Waterproof',
                    'description' => 'Tenda camping model dome modern yang dirancang khusus untuk menghadapi kondisi cuaca outdoor yang tidak menentu, dilengkapi dengan material lapisan luar yang 100% waterproof (anti air). Konstruksi rangkanya menggunakan fiberglass elastis namun kokoh, dirancang aerodinamis agar mampu menahan hembusan angin kencang di dataran tinggi maupun area pantai.

Memiliki kapasitas yang luas dan nyaman untuk menampung 3 hingga 4 orang dewasa berserta perlengkapannya, menjadikannya pilihan tepat untuk camping keluarga. Proses perakitannya sangat intuitif dan cepat, hanya memakan waktu beberapa menit, serta dapat dilipat kembali menjadi bentuk yang sangat ringkas sehingga mudah diselipkan ke dalam carrier atau bagasi motor.',
                    'price' => 250000,
                    'stock' => 25,
                    'category' => 'outdoor',
                    'seed_images' => [
                        ['source' => 'images/product/Tenda.jpg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Kantong Plastik Vakum Pakaian',
                    'description' => 'Solusi penyimpanan revolusioner berupa kantong plastik vakum transparan yang ekstra tebal, elastis, dan tidak mudah bocor atau robek. Alat ini bekerja dengan cara mengeluarkan udara dari dalam kantong menggunakan pompa vakum manual atau vacuum cleaner, mengompres volume pakaian tebal, selimut, maupun bed cover hingga 80% lebih kecil dari ukuran aslinya.

Sangat krusial untuk menghemat ruang penyimpanan di dalam koper saat Anda bersiap untuk traveling jarak jauh, terutama di musim dingin. Selain menghemat tempat, kantong ini juga melindungi pakaian kesayangan Anda dari kelembapan, debu, jamur, serta serangga saat disimpan dalam jangka waktu yang lama di dalam lemari.',
                    'price' => 15000,
                    'stock' => 100,
                    'category' => 'rumah-tangga',
                    'seed_images' => [
                        ['source' => 'images/product/kantongplastikvakum.jpg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Pelapis Kabel Insulasi Tahan Panas',
                    'description' => 'Pelapis atau selongsong kabel insulasi (heat shrink tube) premium yang sangat krusial untuk menjaga keamanan instalasi kelistrikan. Terbuat dari material poliolefin yang mampu menyusut ketat mengikuti bentuk kabel saat dipanaskan, memberikan perlindungan maksimal yang kedap air (waterproof) dan tahan terhadap panas mesin yang ekstrem.

Penggunaan pelapis ini efektif mencegah bahaya korsleting listrik akibat kabel terkelupas, percikan api, atau paparan kelembapan di area mesin maupun kelistrikan rumah tangga. Sangat mudah diaplikasikan hanya bermodalkan heat gun atau korek api, memberikan hasil sambungan kabel yang jauh lebih rapi, profesional, dan aman dibandingkan lakban hitam biasa.',
                    'price' => 25000,
                    'stock' => 80,
                    'category' => 'perkakas',
                    'seed_images' => [
                        ['source' => 'images/product/pelapiskabelinsulasi.jpg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Reel Pancing Spinning Premium',
                    'description' => 'Reel pancing model spinning kelas premium yang dirancang untuk memberikan pengalaman memancing yang presisi dan tak terlupakan, baik di air tawar maupun air laut (saltwater resistant). Dilengkapi dengan sistem bearing berbahan stainless steel yang menghasilkan putaran super halus tanpa suara (silent retrieve), memudahkan Anda mendeteksi gigitan ikan sekecil apa pun.

Sistem drag-nya sangat kuat dan dapat disesuaikan dengan sangat presisi, memberikan kontrol penuh kepada pemancing saat harus berduel menaklukkan perlawanan ikan-ikan berukuran monster. Spool berbahan aluminium alloy-nya yang ringan namun kokoh memungkinkan lontaran senar yang lebih jauh dan akurat, mengurangi risiko senar kusut saat dilempar.',
                    'price' => 120000,
                    'stock' => 30,
                    'category' => 'olahraga',
                    'seed_images' => [
                        ['source' => 'images/product/rellpancing.jpg'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Action Figure Iron Man Mark 85 4 inci',
                    'description' => 'Action figure Iron Man Mark 85 skala 4 inci yang diproduksi secara teliti oleh Top Gear ZD, menampilkan armor ikonis Tony Stark dari pertempuran terakhirnya yang epik. Figur ini menonjolkan tingkat detail pahatan yang sangat tinggi, menangkap setiap lekukan mekanis dan panel armor dengan presisi luar biasa yang jarang ditemukan pada skala sekecil ini.

Diwarnai menggunakan teknik pengecatan metalik premium, cat merah dan emasnya berkilau elegan layaknya logam asli di bawah sorotan lampu. Dilengkapi dengan berbagai titik artikulasi (sendi) yang fleksibel, figur ini dapat diposekan dalam berbagai gaya aksi dinamis, menjadikannya koleksi wajib yang akan memperindah lemari pajangan para penggemar berat Marvel Cinematic Universe.',
                    'price' => 59900,
                    'stock' => 45,
                    'category' => 'action-figure',
                    'seed_images' => [
                        ['source' => 'images/product/Action Figure/Top Gear ZD 4 inci Infinite Action Figure S Iron Man Mark 85 rp 59,900.webp'],
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
                    'description' => 'Kaos parodi kekinian dengan kutipan viral \'Gaji Bercanda Kerja Serius\' yang sukses mencuri perhatian dan menjadi perbincangan hangat di media sosial. Terbuat dari material kain katun combed premium 30s yang terkenal akan kelembutannya, kemampuannya menyerap keringat dengan sangat baik, dan memberikan sensasi adem saat menyentuh kulit meskipun dipakai di cuaca panas.

Desain sablonnya dicetak menggunakan tinta berkualitas tinggi yang lentur, warna tidak mudah pudar, dan tidak akan pecah-pecah meskipun dicuci berkali-kali menggunakan mesin cuci. Dengan potongan unisex yang pas di badan, kaos ini adalah pilihan outfit kasual yang sempurna untuk bersantai di akhir pekan, nongkrong santai di kafe, atau sekadar menyuarakan isi hati para pekerja dengan cara yang humoris.',
                    'price' => 99000,
                    'stock' => 40,
                    'category' => 'kaos',
                    'seed_images' => [
                        ['source' => 'images/product/Pakaian/Baju Parodi Gaji Kerja Serius Nyaman dan Adem - Kaos Viral Gaji Bercanda Kerja Serius Lucu Bisa Request Warna Baju Unisex Bahan Katun - Model 1, XS.jpeg'],
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
                    'description' => 'Atasan pria kasual model t-shirt lengan pendek dari koleksi \'Fire\' oleh Gerald, menawarkan gaya streetwear modern yang simpel namun berkarakter kuat. Potongannya yang regular fit memberikan kenyamanan ruang gerak yang leluasa tanpa terlihat kedodoran, sangat menyanjung berbagai tipe postur tubuh pria.

Desain grafis \'Fire\' yang dicetak secara eksklusif memberikan aksen maskulin dan energik, menjadikannya pilihan outfit yang sangat serbaguna untuk dipadupadankan. Cukup kombinasikan dengan celana jeans favorit, jaket denim, dan sepatu sneakers andalan Anda untuk menciptakan OOTD harian yang stylish, effortlessly cool, dan siap menemani rutinitas urban Anda.',
                    'price' => 48000,
                    'stock' => 30,
                    'category' => 'kaos',
                    'seed_images' => [
                        ['source' => 'images/product/Pakaian/GERALD-bajufasihonatasanpakaiankaosfire-rp48,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'Jagata Coco Top Wanita Cream',
                    'description' => 'Baju atasan wanita elegan Coco Top dari brand Jagata, hadir dalam balutan warna cream pastel yang sangat lembut, kalem, dan memancarkan aura feminin yang kuat. Didesain dengan potongan modern dan aksen jahitan yang rapi, atasan ini memberikan siluet tubuh yang chic, anggun, dan berkelas bagi pemakainya.

Material kainnya jatuh dengan indah, tidak mudah kusut, dan tidak menerawang, memberikan rasa percaya diri ekstra sepanjang hari. Warnanya yang netral membuatnya sangat mudah dipadupadankan (mix and match) dengan berbagai jenis bawahan, mulai dari celana kulot panjang untuk tampilan ngantor yang profesional, hingga rok plisket untuk gaya kasual yang manis saat berjalan-jalan di akhir pekan.',
                    'price' => 189000,
                    'stock' => 25,
                    'category' => 'pakaian-wanita',
                    'seed_images' => [
                        ['source' => 'images/product/Pakaian/Jagata Baju Atasan Wanita Kekinian Coco Top – Cream 104004 Rp 189,000.webp'],
                    ],
                    'has_variants' => false,
                ],
                [
                    'name' => 'T-Shirt Smile Love Crop Top',
                    'description' => 'T-shirt wanita bermodel crop top kekinian yang menggemaskan, dihiasi dengan tipografi grafis \'Smile Love\' yang ceria dan menyebarkan aura positif. Ciri khas utama dari atasan ini adalah detail serut (drawstring) di bagian samping atau depan yang dapat Anda sesuaikan sendiri tarikannya, memberikan sentuhan tekstur unik dan mempertegas siluet tubuh yang proporsional.

Potongan crop-nya yang trendi menjadikannya pasangan sejati bagi celana high-waist, kulot, atau rok mini, menciptakan ilusi kaki yang lebih jenjang. Sangat nyaman dikenakan dan merupakan pilihan outfit paling manis untuk hangout akhir pekan bersama sahabat, pergi ke konser, atau untuk sesi foto OOTD estetik di luar ruangan.',
                    'price' => 39450,
                    'stock' => 45,
                    'category' => 'pakaian-wanita',
                    'seed_images' => [
                        ['source' => 'images/product/Pakaian/thisrt-smile-love-baju-serut-wanita-crop-top-Rp39,450.jpeg'],
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

            $product = $this->products->createForStore($store, $data);

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
                $destPath = 'products/'.Str::random(24).'.'.$ext;
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
