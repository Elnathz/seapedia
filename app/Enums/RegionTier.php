<?php

namespace App\Enums;

/**
 * How far a buyer's shipping address is from a store's origin, by region
 * hierarchy (village ⊂ district ⊂ city ⊂ province). Drives the delivery
 * surcharge added on top of the per-method base fee (§5.4). No coordinates —
 * closeness is an ordinal proxy over stored region strings.
 */
enum RegionTier: string
{
    case SameVillage = 'same_village';
    case SameDistrict = 'same_district';
    case SameCity = 'same_city';
    case SameProvince = 'same_province';
    case Interregional = 'interregional';

    /**
     * Surcharge in IDR added on top of the per-method base fee. Integer IDR,
     * never taxed (§5.2). SameVillage is free so a local delivery keeps the
     * plain base fee.
     */
    public function surcharge(): int
    {
        return match ($this) {
            self::SameVillage => 0,
            self::SameDistrict => 2_000,
            self::SameCity => 5_000,
            self::SameProvince => 10_000,
            self::Interregional => 20_000,
        };
    }

    /**
     * Buyer-facing Indonesian label for the surcharge line.
     */
    public function label(): string
    {
        return match ($this) {
            self::SameVillage => 'Satu kelurahan',
            self::SameDistrict => 'Satu kecamatan',
            self::SameCity => 'Satu kota/kabupaten',
            self::SameProvince => 'Satu provinsi',
            self::Interregional => 'Antar provinsi',
        };
    }
}
