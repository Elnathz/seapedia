<?php

namespace App\Services;

use Illuminate\Support\Arr;

class CatalogService
{
    /**
     * Hardcoded campus-marketplace catalog. Real stores/products backed by
     * the database arrive in Sprint 2 — kept behind this service so that
     * swap only touches this class, never the controller or Vue pages.
     *
     * @var array<int, array<string, mixed>>
     */
    private const PRODUCTS = [
        [
            'slug' => 'kopi-susu-gula-aren',
            'name' => 'Kopi Susu Gula Aren',
            'storeName' => 'Toko Berkah',
            'category' => 'Minuman',
            'price' => 18000,
            'stock' => 24,
            'description' => 'Kopi susu segar dengan gula aren asli, diseduh setiap pagi.',
        ],
        [
            'slug' => 'nasi-goreng-spesial',
            'name' => 'Nasi Goreng Spesial',
            'storeName' => 'Warung Mama Lia',
            'category' => 'Makanan',
            'price' => 22000,
            'stock' => 15,
            'description' => 'Nasi goreng dengan telur, ayam suwir, dan acar timun.',
        ],
        [
            'slug' => 'es-teh-manis',
            'name' => 'Es Teh Manis',
            'storeName' => 'Warung Mama Lia',
            'category' => 'Minuman',
            'price' => 5000,
            'stock' => 40,
            'description' => 'Teh manis dingin, cocok untuk menemani makan siang.',
        ],
        [
            'slug' => 'fotokopi-print-dokumen',
            'name' => 'Fotokopi & Print Dokumen',
            'storeName' => 'Toko Berkah',
            'category' => 'Jasa',
            'price' => 500,
            'stock' => 999,
            'description' => 'Layanan fotokopi dan print per halaman, hitam-putih maupun warna.',
        ],
        [
            'slug' => 'jasa-laundry-kiloan',
            'name' => 'Jasa Laundry Kiloan',
            'storeName' => 'Laundry Kilat',
            'category' => 'Jasa',
            'price' => 7000,
            'stock' => 50,
            'description' => 'Cuci, kering, dan lipat — selesai dalam 1 hari.',
        ],
        [
            'slug' => 'stiker-custom',
            'name' => 'Stiker Custom',
            'storeName' => 'Kreasi Kampus',
            'category' => 'Barang',
            'price' => 10000,
            'stock' => 60,
            'description' => 'Stiker vinyl custom sesuai desain pesanan, tahan air.',
        ],
        [
            'slug' => 'totebag-kanvas',
            'name' => 'Totebag Kanvas',
            'storeName' => 'Kreasi Kampus',
            'category' => 'Barang',
            'price' => 35000,
            'stock' => 18,
            'description' => 'Totebag kanvas tebal, bisa custom sablon.',
        ],
        [
            'slug' => 'snack-box-rapat',
            'name' => 'Snack Box Rapat',
            'storeName' => 'Warung Mama Lia',
            'category' => 'Makanan',
            'price' => 15000,
            'stock' => 30,
            'description' => 'Paket snack untuk rapat atau acara organisasi kampus.',
        ],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function index(?string $search = null): array
    {
        if (! $search) {
            return self::PRODUCTS;
        }

        return array_values(array_filter(
            self::PRODUCTS,
            fn (array $product) => str_contains(
                strtolower($product['name']),
                strtolower($search),
            ),
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        return Arr::first(self::PRODUCTS, fn (array $product) => $product['slug'] === $slug);
    }
}
