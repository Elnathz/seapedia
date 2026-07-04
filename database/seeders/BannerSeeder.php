<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: clear existing banners first so re-running the seeder
        // never duplicates a (placement, sort_order) slot. Without this, a
        // second run doubles every banner and the `limit(2)` in
        // BannerService::sideTopActive() picks two copies of the sort_order=0
        // banner instead of one per slot.
        Banner::query()->delete();

        $banners = [
            // Utama (main)
            [
                'title' => 'Promo Spesial Diskon & Hemat',
                'badge_label' => 'Promo',
                'cta_label' => 'Belanja sekarang',
                'cta_url' => '/catalog',
                'placement' => 'main',
                'image_path' => 'images/banner/utama/promohematbanner.png',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Polygon Indonesia Bisa',
                'badge_label' => 'Eksklusif',
                'cta_label' => 'Lihat Sepeda',
                'cta_url' => '/catalog/polygon-indonesia-bisa',
                'placement' => 'main',
                'image_path' => 'images/banner/utama/sepeda-polygon-indonesia-bisa.png',
                'sort_order' => 1,
                'is_active' => true,
            ],

            // Atas (side_top)
            [
                'title' => 'Panasonic Lumix FZ80D',
                'badge_label' => null,
                'cta_label' => null,
                'cta_url' => '/catalog/panasonic-lumix-fz80d',
                'placement' => 'side_top',
                'image_path' => 'images/banner/atas/Panasonic-lumix-f280D-digital-camera.png',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Action Figure Iron Man Mark 85',
                'badge_label' => null,
                'cta_label' => null,
                'cta_url' => '/catalog/action-figure-iron-man-mark-85-4-inci',
                'placement' => 'side_top',
                'image_path' => 'images/banner/atas/Top Gear ZD 4 inci Infinite Action Figure S Iron Man Mark 85 rp 59,900.png',
                'sort_order' => 1,
                'is_active' => true,
            ],

            // Bawah (side_bottom)
            [
                'title' => 'SteelSeries Apex Pro TKL',
                'badge_label' => null,
                'cta_label' => null,
                'cta_url' => '/catalog/steelseries-apex-pro-tkl-wireless-gen-3',
                'placement' => 'side_bottom',
                'image_path' => 'images/banner/bawah/Apex Pro TKL Wireless Gen 3 rp 3,299,000.png',
                'sort_order' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $b) {
            Banner::create($b);
        }
    }
}
