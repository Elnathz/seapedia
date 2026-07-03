<?php

namespace Tests\Feature\Delivery;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Store;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailableJobsSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_jobs_are_sorted_nearest_first(): void
    {
        $store = Store::factory()->create();

        $makeJob = fn (float $km) => Delivery::factory()->create([
            'status' => DeliveryStatus::Available,
            'order_id' => Order::factory()->create([
                'store_id' => $store->id,
                'delivery_distance_km' => $km,
            ])->id,
        ]);

        // Created out of order so the sort — not insertion order — is exercised.
        $makeJob(30.0);
        $makeJob(3.0);
        $makeJob(12.0);

        $distances = app(DeliveryService::class)
            ->availableJobs()
            ->getCollection()
            ->map(fn (Delivery $delivery) => $delivery->order->delivery_distance_km)
            ->all();

        $this->assertSame([3.0, 12.0, 30.0], $distances);
    }
}
