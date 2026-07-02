<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

foreach (Product::all() as $p) {
    ProductVariant::firstOrCreate(
        ['product_id' => $p->id, 'name' => 'Default'],
        ['price' => $p->price, 'stock' => $p->stock]
    );
    if ($p->image_path) {
        ProductImage::firstOrCreate(
            ['product_id' => $p->id, 'image_path' => $p->image_path],
            ['is_primary' => true]
        );
    }
}
echo "Migrated existing products.\n";
