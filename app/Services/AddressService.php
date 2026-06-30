<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddressService
{
    /**
     * @param  array{recipient_name: string, phone: string, full_address: string, province?: string, city?: string, district?: string, village?: string, postal_code?: string, is_default?: bool}  $data
     */
    public function createForUser(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            // A buyer's very first address is always the default, so there
            // is never a moment with addresses but no default (§ invariant).
            $isDefault = ($data['is_default'] ?? false) || ! $user->addresses()->exists();

            if ($isDefault) {
                $this->unsetCurrentDefault($user);
            }

            return Address::create([
                'user_id' => $user->id,
                'recipient_name' => $data['recipient_name'],
                'phone' => $data['phone'],
                'full_address' => $data['full_address'],
                'province' => $data['province'] ?? null,
                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'village' => $data['village'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'is_default' => $isDefault,
            ]);
        });
    }

    /**
     * @param  array{recipient_name: string, phone: string, full_address: string, province?: string, city?: string, district?: string, village?: string, postal_code?: string, is_default?: bool}  $data
     */
    public function update(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            $isDefault = $data['is_default'] ?? $address->is_default;

            if ($isDefault && ! $address->is_default) {
                $this->unsetCurrentDefault($address->user, $address->id);
            }

            $address->update([
                'recipient_name' => $data['recipient_name'],
                'phone' => $data['phone'],
                'full_address' => $data['full_address'],
                'province' => $data['province'] ?? $address->province,
                'city' => $data['city'] ?? $address->city,
                'district' => $data['district'] ?? $address->district,
                'village' => $data['village'] ?? $address->village,
                'postal_code' => $data['postal_code'] ?? $address->postal_code,
                'is_default' => $isDefault,
            ]);

            return $address->refresh();
        });
    }

    public function setDefault(Address $address): Address
    {
        return DB::transaction(function () use ($address) {
            $this->unsetCurrentDefault($address->user, $address->id);
            $address->update(['is_default' => true]);

            return $address->refresh();
        });
    }

    public function delete(Address $address): void
    {
        DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $userId = $address->user_id;

            $address->delete();

            // Keep the invariant: if a default existed, another address
            // (if any remain) takes over as the default.
            if ($wasDefault) {
                Address::query()
                    ->where('user_id', $userId)
                    ->first()
                    ?->update(['is_default' => true]);
            }
        });
    }

    private function unsetCurrentDefault(User $user, ?int $exceptId = null): void
    {
        Address::query()
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }
}
