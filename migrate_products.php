<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Product::all() as $p) {
    App\Models\ProductVariant::firstOrCreate(
        ['product_id' => $p->id, 'name' => 'Default'],
        ['price' => $p->price, 'stock' => $p->stock]
    );
    if ($p->image_path) {
        App\Models\ProductImage::firstOrCreate(
            ['product_id' => $p->id, 'image_path' => $p->image_path],
            ['is_primary' => true]
        );
    }
}
echo "Migrated existing products.\n";
