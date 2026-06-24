<?php

namespace Database\Seeders;

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
     * Give `seller1` and `multi1` a store with a few image-bearing products,
     * so the public catalog (TDD §13 Day 2) is populated for the demo.
     * Reuses StoreService/ProductService so seed data follows the exact
     * same slug-uniqueness rules as a real seller would (§7).
     */
    public function run(): void
    {
        $this->seedStore(
            username: 'seller1',
            storeName: 'Toko Berkah',
            description: 'Toko kelontong dan jasa cetak di lingkungan kampus.',
            products: [
                ['name' => 'Kopi Susu Gula Aren', 'description' => 'Kopi susu segar dengan gula aren asli, diseduh setiap pagi.', 'price' => 18_000, 'stock' => 24],
                ['name' => 'Fotokopi & Print Dokumen', 'description' => 'Layanan fotokopi dan print per halaman, hitam-putih maupun warna.', 'price' => 500, 'stock' => 999],
                ['name' => 'Stiker Custom', 'description' => 'Stiker vinyl custom sesuai desain pesanan, tahan air.', 'price' => 10_000, 'stock' => 60],
            ],
        );

        $this->seedStore(
            username: 'multi1',
            storeName: 'Warung Mama Lia',
            description: 'Warung makan dan minuman, favorit anak kos sekitar kampus.',
            products: [
                ['name' => 'Nasi Goreng Spesial', 'description' => 'Nasi goreng dengan telur, ayam suwir, dan acar timun.', 'price' => 22_000, 'stock' => 15],
                ['name' => 'Es Teh Manis', 'description' => 'Teh manis dingin, cocok untuk menemani makan siang.', 'price' => 5_000, 'stock' => 40],
                ['name' => 'Snack Box Rapat', 'description' => 'Paket snack untuk rapat atau acara organisasi kampus.', 'price' => 15_000, 'stock' => 30],
            ],
        );
    }

    /**
     * @param  array<int, array{name: string, description: string, price: int, stock: int}>  $products
     */
    private function seedStore(string $username, string $storeName, string $description, array $products): void
    {
        $user = User::query()->where('username', $username)->first();

        if (! $user) {
            return;
        }

        $store = $this->stores->createForUser($user, [
            'name' => $storeName,
            'description' => $description,
        ]);

        foreach ($products as $data) {
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
