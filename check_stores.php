$store = App\Models\Store::where('slug','style-house')->withCount('products')->first();
echo "Store: {$store->name}, products_count: {$store->products_count}\n";
echo "Products:\n";
echo $store->products()->get(['id','name','price','is_active','image_path'])->toJson(JSON_PRETTY_PRINT);
