<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Enums\SlaUrgency;
use App\Models\Order;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaUrgencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_enum_classifies_by_distance_to_deadline(): void
    {
        $now = CarbonImmutable::parse('2026-07-04 12:00:00');

        $this->assertSame(SlaUrgency::Normal, SlaUrgency::forDueDate($now->addDays(2), $now, false));
        $this->assertSame(SlaUrgency::Critical, SlaUrgency::forDueDate($now->addHours(12), $now, false));
        // The one-tick boundary is inclusive — exactly one day out is still Critical.
        $this->assertSame(SlaUrgency::Critical, SlaUrgency::forDueDate($now->addDay(), $now, false));
        $this->assertSame(SlaUrgency::Overdue, SlaUrgency::forDueDate($now->subHour(), $now, false));
    }

    public function test_final_orders_are_never_at_risk(): void
    {
        $now = CarbonImmutable::parse('2026-07-04 12:00:00');

        // A finished order sitting past its deadline must not read as overdue.
        $this->assertSame(SlaUrgency::Normal, SlaUrgency::forDueDate($now->subDays(3), $now, true));
        $this->assertSame(SlaUrgency::Normal, SlaUrgency::forDueDate(null, $now, false));
    }

    public function test_ticks_remaining_rounds_up_and_signs(): void
    {
        $now = CarbonImmutable::parse('2026-07-04 12:00:00');

        $this->assertSame(2, SlaUrgency::ticksRemaining($now->addDays(2), $now));
        $this->assertSame(1, SlaUrgency::ticksRemaining($now->addHours(12), $now));
        $this->assertLessThanOrEqual(0, SlaUrgency::ticksRemaining($now->subHour(), $now));
    }

    public function test_seller_order_list_floats_at_risk_orders_to_the_top(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $now = now();

        $overdue = Order::factory()->create([
            'store_id' => $store->id,
            'buyer_id' => $buyer->id,
            'status' => OrderStatus::MenungguPengirim,
            'sla_due_at' => $now->copy()->subHour(),
        ]);
        $critical = Order::factory()->create([
            'store_id' => $store->id,
            'buyer_id' => $buyer->id,
            'status' => OrderStatus::SedangDikemas,
            'sla_due_at' => $now->copy()->addHours(2),
        ]);
        $calm = Order::factory()->create([
            'store_id' => $store->id,
            'buyer_id' => $buyer->id,
            'status' => OrderStatus::SedangDikemas,
            'sla_due_at' => $now->copy()->addDays(3),
        ]);
        $finished = Order::factory()->create([
            'store_id' => $store->id,
            'buyer_id' => $buyer->id,
            'status' => OrderStatus::PesananSelesai,
            'sla_due_at' => $now->copy()->subDays(2),
        ]);

        $response = $this->actingAs($seller)
            ->withSession(['active_role' => RoleName::Seller->value])
            ->get(route('seller.orders.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            // Overdue (soonest deadline) first, then critical, then calm, then the finished tail.
            ->where('orders.data.0.id', $overdue->id)
            ->where('orders.data.0.sla_urgency', 'overdue')
            ->where('orders.data.1.id', $critical->id)
            ->where('orders.data.1.sla_urgency', 'critical')
            ->where('orders.data.2.id', $calm->id)
            ->where('orders.data.2.sla_urgency', 'normal')
            ->where('orders.data.3.id', $finished->id)
            ->where('orders.data.3.sla_urgency', 'normal')
        );
    }

    private function userWithRole(RoleName $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => $role->value])->id,
        );

        return $user;
    }
}
