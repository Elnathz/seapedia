<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the platform-wide category taxonomy (two levels: parent → child).
     * Categories are admin-curated; sellers only pick from this list when
     * creating products. Icons are Lucide component names used by the UI.
     *
     * @var array<int, array{name: string, icon: string, children: array<int, string>}>
     */
    private array $tree = [
        ['name' => 'Makanan', 'icon' => 'UtensilsCrossed', 'children' => ['Makanan Berat', 'Cemilan']],
        ['name' => 'Minuman', 'icon' => 'CupSoda', 'children' => ['Kopi', 'Teh', 'Jus']],
        ['name' => 'Elektronik', 'icon' => 'Smartphone', 'children' => ['Handphone', 'Aksesori HP', 'Audio']],
        ['name' => 'Fashion', 'icon' => 'Shirt', 'children' => ['Pria', 'Wanita']],
        ['name' => 'Kebutuhan Harian', 'icon' => 'ShoppingBasket', 'children' => ['Sembako', 'Perawatan', 'Perkakas']],
        ['name' => 'Hobi & Outdoor', 'icon' => 'Tent', 'children' => ['Alat Pancing', 'Outdoor']],
        ['name' => 'Lainnya', 'icon' => 'Package', 'children' => ['Umum']],
    ];

    public function run(): void
    {
        foreach ($this->tree as $order => $parent) {
            $root = Category::query()->firstOrCreate(
                ['slug' => Str::slug($parent['name'])],
                [
                    'parent_id' => null,
                    'name' => $parent['name'],
                    'icon' => $parent['icon'],
                    'is_active' => true,
                    'sort_order' => $order,
                ]
            );

            foreach ($parent['children'] as $childOrder => $childName) {
                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $root->id,
                        'name' => $childName,
                        'icon' => null,
                        'is_active' => true,
                        'sort_order' => $childOrder,
                    ]
                );
            }
        }
    }
}
