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
                [
                    'name' => 'Kopi Susu Gula Aren Premium', 
                    'description' => "Nikmati kelezatan Kopi Susu Gula Aren Premium dari Toko Berkah yang diracik khusus untuk menemani waktu begadang atau nongkrong Anda. Menggunakan biji kopi pilihan jenis Arabica blend yang di-roasting medium-dark untuk menghasilkan aroma kopi yang kuat namun tetap ramah di lambung.\n\nDipadukan dengan susu segar berkualitas dan gula aren asli yang legit alami tanpa pemanis buatan, menciptakan perpaduan rasa pahit kopi dan manis creamy yang sangat seimbang. Sangat pas disajikan dingin di siang hari yang terik atau hangat di malam hari.\n\nTersedia dalam beberapa varian ukuran sesuai kebutuhan Anda, mulai dari cup standar untuk dinikmati sendiri, hingga botol literan yang cocok untuk stok di kulkas kosan atau diminum beramai-ramai.",
                    'price' => 18_000, 
                    'stock' => 50, 
                    'category' => 'kopi',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Cup Reguler (14oz)', 'price' => 18_000, 'stock' => 30],
                        ['name' => 'Cup Large (16oz)', 'price' => 22_000, 'stock' => 15],
                        ['name' => 'Botol 1 Liter', 'price' => 85_000, 'stock' => 5],
                    ],
                ],
                [
                    'name' => 'Stiker Vinyl Custom Anti Air', 
                    'description' => "Cetak stiker desain Anda sendiri dengan kualitas premium! Stiker vinyl custom kami menggunakan bahan dasar plastik yang 100% anti air, anti robek, dan memiliki daya rekat ekstra kuat. Sangat cocok diaplikasikan pada helm, laptop, motor, tumblr, hingga kemasan produk jualan Anda.\n\nDicetak menggunakan mesin digital printing resolusi tinggi yang menjamin warna tajam, detail yang presisi, dan tidak mudah pudar meskipun terpapar sinar matahari langsung. Anda bisa memilih hasil akhir (finishing) sesuai selera, baik glossy yang mengkilap maupun doff/matte yang elegan.\n\nKami melayani pemesanan tanpa minimal order yang memberatkan. Cukup kirimkan desain Anda dalam format PNG atau PDF, dan kami akan memotongnya secara presisi (kiss cut atau die cut) sesuai kontur gambar.", 
                    'price' => 10_000, 
                    'stock' => 100, 
                    'category' => 'umum',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'A3+ Glossy (Kiss Cut)', 'price' => 15_000, 'stock' => 50],
                        ['name' => 'A3+ Doff (Kiss Cut)', 'price' => 15_000, 'stock' => 50],
                    ],
                ],
            ],
        );

        $this->seedStore(
            email: 'multi1@seapedia.test',
            storeName: 'Warung Mama Lia',
            description: 'Warung makan dan minuman, favorit anak kos sekitar kampus.',
            products: [
                [
                    'name' => 'Nasi Goreng Spesial Mama Lia', 
                    'description' => "Nasi Goreng Spesial racikan rahasia Mama Lia yang sudah melegenda di kalangan mahasiswa kampus. Digoreng menggunakan wajan baja tebal dengan api besar (wok hei) untuk menghasilkan aroma smokey yang menggugah selera. Menggunakan beras pera pilihan yang tidak lembek saat digoreng.\n\nPorsi kuli yang sangat mengenyangkan, disajikan lengkap dengan suwiran ayam kampung, telur mata sapi setengah matang, kerupuk udang renyah, dan acar timun segar. Bumbu rempahnya meresap sempurna hingga ke setiap butir nasi, dijamin membuat Anda ketagihan sejak suapan pertama.\n\nAnda bisa memesan tingkat kepedasan sesuai selera, mulai dari tidak pedas sama sekali hingga pedas gila. Tersedia juga varian tambahan topping seperti sosis, bakso, atau ati ampela.", 
                    'price' => 22_000, 
                    'stock' => 30, 
                    'category' => 'makanan-berat',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Pedas Sedang + Telur Dadar', 'price' => 22_000, 'stock' => 15],
                        ['name' => 'Pedas Gila + Telur Ceplok', 'price' => 22_000, 'stock' => 15],
                    ],
                ],
                [
                    'name' => 'Snack Box Acara Kampus', 
                    'description' => "Solusi konsumsi praktis untuk berbagai kegiatan organisasi, rapat BEM, kepanitiaan, atau seminar kampus. Snack Box Warung Mama Lia dikemas dalam kotak higienis yang rapi dan elegan, memudahkan distribusi kepada peserta acara tanpa perlu repot menyiapkan piring.\n\nSetiap box berisi kombinasi kue basah tradisional dan modern yang dibuat segar (freshly baked) setiap pagi tanpa bahan pengawet. Tersedia dalam berbagai pilihan paket yang bisa disesuaikan dengan budget acara Anda, mulai dari paket ekonomis hingga paket premium.\n\nKami siap menerima pesanan dalam jumlah besar (hingga ribuan box) dengan jaminan ketepatan waktu pengiriman. Silakan pilih varian paket yang paling sesuai dengan kebutuhan acara Anda.", 
                    'price' => 15_000, 
                    'stock' => 100, 
                    'category' => 'cemilan',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Paket Ekonomis (3 Kue + Air)', 'price' => 10_000, 'stock' => 50],
                        ['name' => 'Paket Standar (4 Kue + Teh Kotak)', 'price' => 15_000, 'stock' => 50],
                        ['name' => 'Paket Premium (5 Kue + Kopi/Jus)', 'price' => 20_000, 'stock' => 50],
                    ],
                ],
            ],
        );

        // Additional sellers — each gets 5-8 products across different categories.
        // Skip if store already exists (idempotent — called after seller1/multi1 above).
        $this->seedStore(
            email: 'seller2@seapedia.test',
            storeName: 'Kedai Kopi Nusantara',
            description: 'Berbagai varian kopi dari seluruh Indonesia, bisa biji atau sachet.',
            products: [
                [
                    'name' => 'Biji Kopi Arabica Toraja Sapan', 
                    'description' => "Biji kopi Arabica single origin dari dataran tinggi Sapan, Toraja. Ditanam di ketinggian 1600 mdpl yang menghasilkan profil rasa kompleks dengan keasaman (acidity) medium-high yang menyegarkan. Anda akan menemukan notes rempah (spices), dark chocolate, dan hint herbal yang sangat unik dan khas Toraja.\n\nKami melakukan proses roasting (penyangraian) dalam batch kecil setiap minggunya untuk menjamin kesegaran kopi yang Anda terima. Level roasting medium sangat cocok untuk diseduh menggunakan metode manual brew seperti V60, Chemex, atau Kalita Wave.\n\nTersedia dalam bentuk biji utuh (whole bean) agar kesegarannya terjaga lebih lama, atau Anda bisa memilih varian giling kasar/halus sesuai dengan alat seduh yang Anda miliki di rumah.", 
                    'price' => 75_000, 
                    'stock' => 20, 
                    'category' => 'kopi',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Biji Utuh 250g', 'price' => 75_000, 'stock' => 10],
                        ['name' => 'Giling V60 250g', 'price' => 75_000, 'stock' => 5],
                        ['name' => 'Giling Espresso 250g', 'price' => 75_000, 'stock' => 5],
                    ],
                ],
                [
                    'name' => 'Kopi Robusta Gayo Premium', 
                    'description' => "Kopi Robusta Gayo pilihan dengan tingkat kepekatan (body) yang sangat tebal dan rasa pahit yang mantap. Cocok untuk Anda yang membutuhkan asupan kafein tinggi di pagi hari atau sebagai bahan dasar campuran es kopi susu kekinian.\n\nDi-roasting hingga level dark untuk memaksimalkan aroma gosong karamel dan cokelat pekat. Karena bodinya yang kuat, kopi ini sangat pas jika dipadukan dengan krimer kental manis atau susu full cream tanpa kehilangan karakter aslinya.\n\nDikemas dalam standing pouch aluminium foil yang dilengkapi dengan zipper lock dan one-way valve untuk mengeluarkan gas CO2 pasca-roasting sekaligus mencegah udara luar masuk merusak kopi.", 
                    'price' => 25_000, 
                    'stock' => 35, 
                    'category' => 'kopi',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Biji Utuh 100g', 'price' => 25_000, 'stock' => 20],
                        ['name' => 'Giling Halus (Tubruk) 100g', 'price' => 25_000, 'stock' => 15],
                    ],
                ],
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
                [
                    'name' => 'Nasi Box Ayam Geprek Spesial', 
                    'description' => "Paket komplit Nasi Box Ayam Geprek Spesial dari Dapur Mama Diah, pilihan nomor satu untuk makan siang yang mengenyangkan. Menggunakan beras pulen kualitas premium dan potongan ayam segar berukuran besar yang digoreng krispi dengan bumbu rempah rahasia.\n\nSetiap porsinya dilengkapi dengan sambal bawang geprek yang pedasnya nendang, lalapan segar (kol, timun, kemangi), dan taburan bawang goreng renyah. Sambalnya diulek dadakan menggunakan cabai rawit merah pilihan untuk menjaga kesegaran dan cita rasa pedas alaminya.\n\nSangat cocok untuk dipesan sebagai menu makan siang harian, acara syukuran, atau konsumsi kepanitiaan kampus. Dikemas dalam kotak bento eksklusif yang tahan panas dan anti bocor, sehingga aman saat proses pengiriman.", 
                    'price' => 25_000, 
                    'stock' => 30, 
                    'category' => 'makanan-berat',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Geprek Original (Dada)', 'price' => 25_000, 'stock' => 15],
                        ['name' => 'Geprek Keju Mozzarella', 'price' => 30_000, 'stock' => 15],
                    ],
                ],
                [
                    'name' => 'Mie Goreng Seafood Melimpah', 
                    'description' => "Mie Goreng ala restoran Chinese Food yang disajikan dengan porsi jumbo dan isian seafood yang sangat melimpah. Menggunakan mie telur kenyal yang dimasak dengan suhu tinggi (high heat) untuk menghasilkan aroma khas masakan wajan besi (wok hei) yang menggoda selera.\n\nDiperkaya dengan potongan udang kupas segar, cumi-cumi kenyal, bakso ikan, telur orak-arik, dan aneka sayuran hijau (sawi, kubis). Bumbu saus tiram dan kecap manis racikan sendiri membuatnya memiliki perpaduan rasa gurih, manis, dan sedikit pedas lada putih yang sangat pas di lidah.\n\nBisa di-request untuk tidak menggunakan MSG atau menyesuaikan tingkat kepedasan. Kami menjamin setiap bahan seafood yang digunakan adalah fresh catch (tangkapan segar) harian.", 
                    'price' => 35_000, 
                    'stock' => 25, 
                    'category' => 'makanan-berat',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Level Pedas 0 (Tidak Pedas)', 'price' => 35_000, 'stock' => 10],
                        ['name' => 'Level Pedas 3 (Sedang)', 'price' => 35_000, 'stock' => 15],
                    ],
                ],
            ],
        );

        $this->seedStore(
            email: 'seller5@seapedia.test',
            storeName: 'Style House',
            description: 'Pakaian trendy dan affordable untuk mahasiswa.',
            products: [
                [
                    'name' => 'Kemeja Flanel Kotak Premium', 
                    'description' => "Tampil stylish dan kasual dengan Kemeja Flanel Kotak Premium dari Style House. Dibuat menggunakan material 100% katun flanel impor yang sangat lembut, tebal namun tidak membuat gerah saat dipakai beraktivitas seharian di kampus atau saat hangout bersama teman.\n\nPotongan pola regular fit dirancang khusus untuk pas di bentuk badan pria Asia, memberikan siluet yang proporsional tanpa terlihat kebesaran. Jahitannya sangat rapi dan presisi dengan menggunakan benang nylon kuat, memastikan kemeja ini awet dan tidak mudah robek pada bagian ketiak atau kancing.\n\nKemeja ini sangat serbaguna, bisa dipakai sebagai pakaian utama yang dikancingkan rapi, atau dijadikan outer (luaran) dengan paduan kaos polos di dalamnya. Warna kain telah melalui proses pre-washed sehingga tidak akan luntur atau menyusut saat dicuci.", 
                    'price' => 89_000, 
                    'stock' => 20, 
                    'category' => 'pria',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Kotak Merah Hitam (L)', 'price' => 89_000, 'stock' => 10],
                        ['name' => 'Kotak Biru Dongker (XL)', 'price' => 89_000, 'stock' => 10],
                    ],
                ],
                [
                    'name' => 'Sling Bag Canvas Minimalis', 
                    'description' => "Tas selempang (sling bag) berbahan kanvas tebal dengan desain minimalis ala Korea, pilihan tepat untuk menemani aktivitas harian para mahasiswi. Ukurannya sangat pas, tidak terlalu besar namun memiliki kapasitas (kompartemen) yang cukup untuk membawa dompet, HP, makeup pouch, dan charger.\n\nDilengkapi dengan tali selempang yang dapat diatur panjang-pendeknya (adjustable strap) dan menggunakan material hardware besi anti karat yang mewah. Di bagian dalam terdapat lapisan furing satin anti air dan kantong kecil ber-ritsleting untuk menyimpan barang berharga seperti kunci motor atau kartu-kartu penting.\n\nTersedia dalam berbagai pilihan warna pastel yang estetik dan mudah dipadu-padankan dengan outfit keseharian Anda. Mudah dibersihkan (washable) jika terkena noda ringan.", 
                    'price' => 75_000, 
                    'stock' => 18, 
                    'category' => 'wanita',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Warna Beige', 'price' => 75_000, 'stock' => 9],
                        ['name' => 'Warna Sage Green', 'price' => 75_000, 'stock' => 9],
                    ],
                ],
            ],
        );

        $this->seedStore(
            email: 'seller6@seapedia.test',
            storeName: 'Sembako Sejahtera',
            description: 'Sembako lengkap dengan harga grosir untuk mahasiswa dan warga.',
            products: [
                [
                    'name' => 'Beras IR64 Super Pulen', 
                    'description' => "Beras kelas premium jenis IR64 asli dari hasil panen petani lokal pilihan. Beras ini terkenal dengan karakteristiknya yang sangat putih, bersih tanpa gabah/batu kecil, dan tidak menggunakan bahan pemutih atau pengawet kimia berbahaya.\n\nSaat dimasak, beras ini akan menghasilkan nasi yang bertekstur sangat pulen, mengembang dengan sempurna, dan tidak mudah basi atau menguning jika disimpan di dalam magic com selama 24 jam. Sangat cocok disajikan dengan berbagai lauk pauk khas Indonesia.\n\nDikemas dalam karung laminasi tebal yang aman dari kutu beras dan kelembapan. Kemasan ekonomis ini sangat pas untuk stok bulanan anak kos maupun keluarga kecil. Harga dijamin lebih murah dibanding beli di minimarket!", 
                    'price' => 75_000, 
                    'stock' => 30, 
                    'category' => 'sembako',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Kemasan Karung 5kg', 'price' => 75_000, 'stock' => 20],
                        ['name' => 'Kemasan Karung 10kg', 'price' => 145_000, 'stock' => 10],
                    ],
                ],
                [
                    'name' => 'Minyak Goreng Kelapa Sawit', 
                    'description' => "Minyak goreng berkualitas tinggi yang diproses dari buah kelapa sawit segar pilihan melalui 5 kali proses penyaringan (filtering) mutakhir. Menghasilkan minyak yang sangat jernih, berwarna keemasan, dan tidak mudah keruh atau beku meskipun disimpan di tempat dingin.\n\nKandungan omega 9 dan Vitamin A alaminya tetap terjaga berkat teknologi pemanasan suhu rendah. Minyak ini juga lebih hemat saat digunakan karena tidak cepat menghitam setelah beberapa kali penggorengan, membuat masakan Anda matang lebih merata dan ekstra renyah (crispy).\n\nSangat direkomendasikan untuk menggoreng segala jenis makanan mulai dari lauk pauk, kerupuk, hingga aneka gorengan. Dilengkapi kemasan pouch tebal anti-bocor atau jerigen tebal yang aman dikirim menggunakan ekspedisi.", 
                    'price' => 35_000, 
                    'stock' => 50, 
                    'category' => 'sembako',
                    'has_variants' => true,
                    'variants' => [
                        ['name' => 'Pouch Refill 2 Liter', 'price' => 35_000, 'stock' => 30],
                        ['name' => 'Jerigen 5 Liter', 'price' => 85_000, 'stock' => 20],
                    ],
                ],
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
