<?php

namespace Database\Seeders;

use App\Enums\DeliveryMethod;
use App\Models\Address;
use App\Models\Product;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\TopupService;
use Illuminate\Database\Seeder;

class BuyerDemoSeeder extends Seeder
{
    public function __construct(
        private readonly TopupService $topups,
        private readonly AddressService $addresses,
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * Gives buyer1 and multi1 a topped-up wallet (via TopupService, so the
     * ledger has a real entry, not a raw balance write) and a saved
     * address, then walks buyer1 through one real checkout against
     * seller1's store so order history (buyer side) and the incoming
     * orders list (seller side) are populated for the demo — all through
     * the same Services a real user would hit, per §12 and golden rule 2.
     */
    public function run(): void
    {
        $buyer = User::query()->where('username', 'buyer1')->first();
        $multi = User::query()->where('username', 'multi1')->first();

        if ($buyer) {
            $this->topups->create($buyer, 500_000);

            $address = $this->addresses->createForUser($buyer, [
                'recipient_name' => 'Buyer One',
                'phone' => '081234567890',
                'full_address' => 'Jl. Kampus No. 1, Semarang',
            ]);

            $this->seedSampleOrder($buyer, $address);
        }

        if ($multi) {
            $this->topups->create($multi, 300_000);

            $this->addresses->createForUser($multi, [
                'recipient_name' => 'Multi Role',
                'phone' => '089876543210',
                'full_address' => 'Jl. Mahasiswa No. 2, Semarang',
            ]);
        }
    }

    private function seedSampleOrder(User $buyer, Address $address): void
    {
        $seller = User::query()->where('username', 'seller1')->first();
        $product = $seller?->store?->products()->first();

        if (! $product instanceof Product) {
            return;
        }

        $this->carts->addItem($buyer, $product, 1);
        $this->checkout->commit($buyer, $address, DeliveryMethod::Regular);
    }
}
