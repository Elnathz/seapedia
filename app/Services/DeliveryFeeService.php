<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Models\Address;
use App\Models\Store;

/**
 * Distance- and weight-based delivery fee (§5.4). The fee is:
 *
 *   fee = base(method) + billable_km × rate_per_km(method) + weight_fee
 *
 * where the distance is the Haversine great-circle distance between the store
 * origin and the buyer's shipping address (both picked on a map, stored as
 * lat/lng — no runtime geocoding), billed up to a cap, and the weight fee is a
 * per-kilogram surcharge over the first free kilogram. The per-method base and
 * per-km rate keep every method distinct (spec line 278). Every input is known
 * at checkout, so preview() and commit() compute the same number — the quote
 * equals the charge. Framework-agnostic; reused by web + /api/v1.
 */
class DeliveryFeeService
{
    /** Distance is billed up to this many km; beyond it the per-km part is flat. */
    private const KM_CAP = 80;

    /** The first kilogram ships free; each started kg beyond it costs a step. */
    private const FREE_WEIGHT_GRAMS = 1_000;

    private const WEIGHT_STEP_GRAMS = 1_000;

    private const WEIGHT_STEP_FEE = 2_000;

    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Total delivery fee for a method, origin, destination and order weight.
     */
    public function fee(DeliveryMethod $method, ?Store $store, ?Address $address, int $weightGrams = 0): int
    {
        return $this->breakdown($method, $store, $address, $weightGrams)['total'];
    }

    /**
     * The itemised fee so the checkout summary can show base / distance / weight.
     *
     * @return array{base_fee:int, distance_km:float, billable_km:int, distance_fee:int, weight_grams:int, weight_fee:int, total:int}
     */
    public function breakdown(DeliveryMethod $method, ?Store $store, ?Address $address, int $weightGrams = 0): array
    {
        $km = $this->distanceKm($store, $address);
        $billableKm = $this->billableKm($km);
        $baseFee = $method->fee();
        $distanceFee = $billableKm * $method->ratePerKm();
        $weightFee = $this->weightFee($weightGrams);

        return [
            'base_fee' => $baseFee,
            'distance_km' => round($km, 1),
            'billable_km' => $billableKm,
            'distance_fee' => $distanceFee,
            'weight_grams' => $weightGrams,
            'weight_fee' => $weightFee,
            'total' => $baseFee + $distanceFee + $weightFee,
        ];
    }

    /**
     * Great-circle km between store origin and address. 0 when either point is
     * missing a coordinate — never surprise-charge on incomplete geo data.
     */
    public function distanceKm(?Store $store, ?Address $address): float
    {
        if (! $store || ! $address) {
            return 0.0;
        }

        $lat1 = $store->origin_latitude;
        $lng1 = $store->origin_longitude;
        $lat2 = $address->latitude;
        $lng2 = $address->longitude;

        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return 0.0;
        }

        return $this->haversine((float) $lat1, (float) $lng1, (float) $lat2, (float) $lng2);
    }

    private function billableKm(float $km): int
    {
        return min((int) ceil($km), self::KM_CAP);
    }

    private function weightFee(int $grams): int
    {
        if ($grams <= self::FREE_WEIGHT_GRAMS) {
            return 0;
        }

        $extraKg = (int) ceil(($grams - self::FREE_WEIGHT_GRAMS) / self::WEIGHT_STEP_GRAMS);

        return $extraKg * self::WEIGHT_STEP_FEE;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * asin(min(1.0, sqrt($a)));
    }
}
