<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Enums\RegionTier;
use App\Models\Address;
use App\Models\Store;

/**
 * Delivery fee = per-method base fee (§5.4) + region-tier surcharge. The
 * surcharge reflects how far the buyer's shipping address is from the store's
 * origin, using stored region strings (no coordinates). Base fee still differs
 * per method (spec line 278). The whole delivery fee — base and surcharge — is
 * never taxed (§5.2). Framework-agnostic; reused by web + /api/v1 checkout.
 */
class DeliveryFeeService
{
    /**
     * Total delivery fee for a method given the store origin and destination.
     */
    public function fee(DeliveryMethod $method, ?Store $store, ?Address $address): int
    {
        return $method->fee() + $this->tier($store, $address)->surcharge();
    }

    /**
     * Just the surcharge component (0 when local or origin unknown).
     */
    public function surcharge(?Store $store, ?Address $address): int
    {
        return $this->tier($store, $address)->surcharge();
    }

    /**
     * Closeness tier by region hierarchy. When the store has no recorded
     * origin province, closeness is unknown and we fall back to SameVillage
     * (0 surcharge) rather than surprise-charging the buyer for missing data.
     */
    public function tier(?Store $store, ?Address $address): RegionTier
    {
        if (! $store || ! $address || $this->norm($store->province) === null) {
            return RegionTier::SameVillage;
        }

        $sameProvince = $this->matches($store->province, $address->province);
        $sameCity = $sameProvince && $this->matches($store->city, $address->city);
        $sameDistrict = $sameCity && $this->matches($store->district, $address->district);
        $sameVillage = $sameDistrict && $this->matches($store->village, $address->village);

        return match (true) {
            $sameVillage => RegionTier::SameVillage,
            $sameDistrict => RegionTier::SameDistrict,
            $sameCity => RegionTier::SameCity,
            $sameProvince => RegionTier::SameProvince,
            default => RegionTier::Interregional,
        };
    }

    private function matches(?string $a, ?string $b): bool
    {
        $a = $this->norm($a);
        $b = $this->norm($b);

        return $a !== null && $a === $b;
    }

    private function norm(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtolower($value);
    }
}
