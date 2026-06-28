<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $main = [
            ['title' => 'Gratis ongkir pesanan pertama', 'badge_label' => 'Promo', 'cta_label' => 'Belanja sekarang', 'cta_url' => '/catalog'],
            ['title' => 'Diskon kuliner kampus', 'cta_label' => 'Lihat menu', 'cta_url' => '/catalog?category=makanan'],
            ['title' => 'Jadi kurir kampus', 'cta_label' => 'Daftar kurir', 'cta_url' => '/register'],
        ];

        foreach ($main as $i => $b) {
            Banner::create([
                ...$b,
                'placement' => 'main',
                'image_path' => 'images/banners/banner-main-'.($i + 1).'.png',
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $side = [
            ['title' => 'Elektronik', 'cta_url' => '/catalog?category=elektronik'],
            ['title' => 'Fashion', 'cta_url' => '/catalog?category=fashion'],
            ['title' => 'Minuman', 'cta_url' => '/catalog?category=minuman'],
            ['title' => 'Kebutuhan Harian', 'cta_url' => '/catalog?category=kebutuhan-harian'],
        ];

        foreach ($side as $i => $b) {
            Banner::create([
                ...$b,
                'placement' => 'side',
                'image_path' => 'images/banners/banner-side-'.($i + 1).'.png',
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }
}
