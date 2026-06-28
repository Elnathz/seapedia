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
     * Gives every buyer (buyer1..buyer3 + multi1) a topped-up wallet and a
     * saved address via the same Services a real user would hit (§12, golden
     * rule 2). buyer1 also walks through one full checkout with both demo
     * discount codes (PROMO20K + HEMAT10) to exercise the §5.3 combination
     * rule and populate the buyer's/seller's spending reports.
     *
     * The "processing" delay (§ Sprint 6 T2) is a UX affordance — seeders
     * skip it by setting `payment.topup.processing_seconds => 0`.
     */
    public function run(): void
    {
        config(['payment.topup.processing_seconds' => 0]);

        // buyer1: full checkout with discount codes.
        $buyer1 = User::query()->where('username', 'buyer1')->first();
        if ($buyer1) {
            $this->topupIfEmpty($buyer1, 500_000);
            $address = $this->seedAddressIfMissing($buyer1, 'Buyer One', '081234567890', 'Jl. Kampus No. 1, Semarang');
            $this->seedSampleOrder($buyer1, $address);
        }

        // buyer2: topped up, no orders yet.
        $buyer2 = User::query()->where('username', 'buyer2')->first();
        if ($buyer2) {
            $this->topupIfEmpty($buyer2, 300_000);
            $this->seedAddressIfMissing($buyer2, 'Buyer Two', '082233445566', 'Jl. Kost Biru No. 2, Semarang');
        }

        // buyer3: topped up, no orders yet.
        $buyer3 = User::query()->where('username', 'buyer3')->first();
        if ($buyer3) {
            $this->topupIfEmpty($buyer3, 200_000);
            $this->seedAddressIfMissing($buyer3, 'Buyer Three', '083344556677', 'Jl. Asrama UNDIP No. 3, Semarang');
        }

        // multi1: topped up, has a store but no buyer orders yet.
        $multi = User::query()->where('username', 'multi1')->first();
        if ($multi) {
            $this->topupIfEmpty($multi, 300_000);
            $this->seedAddressIfMissing($multi, 'Multi Role', '089876543210', 'Jl. Mahasiswa No. 2, Semarang');
        }
    }

    private function seedSampleOrder(User $buyer, Address $address): void
    {
        $seller = User::query()->where('username', 'seller1')->first();
        $product = $seller?->store?->products()->first();

        if (! $product instanceof Product) {
            return;
        }

        // 6 units clears PROMO20K's 100,000 min_spend so both demo codes
        // (PROMO20K + HEMAT10) apply together, exercising the §5.3
        // combination rule with real seeded data.
        $this->carts->addItem($buyer, $product, 6);
        $this->checkout->commit($buyer, $address, DeliveryMethod::Regular, 'PROMO20K', 'HEMAT10');
    }

    private function seedAddressIfMissing(User $user, string $recipientName, string $phone, string $address): Address
    {
        if ($user->addresses()->exists()) {
            return $user->addresses()->first();
        }

        return $this->addresses->createForUser($user, [
            'recipient_name' => $recipientName,
            'phone' => $phone,
            'full_address' => $address,
        ]);
    }

    private function topupIfEmpty(User $user, int $amount): void
    {
        if ($user->wallet->balance <= 0) {
            $this->topups->checkStatus($this->topups->create($user, $amount));
        }
    }
}
