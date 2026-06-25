<?php

namespace Tests\Feature\Security;

use App\Enums\RoleName;
use App\Models\Delivery;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the driver-job IDOR closed in Sprint 6 T1:
 * `DriverJobController@show` had no ownership check, so any authenticated
 * driver could view any other driver's taken job (order items, store name,
 * driver name) just by guessing the delivery id (§L7B cross-user job access).
 */
class OwnershipAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(RoleName $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => $role->value])->id,
        );

        return $user;
    }

    private function actingAsRole(User $user, RoleName $role)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => $role->value]);
    }

    public function test_a_driver_can_preview_an_unclaimed_job(): void
    {
        $delivery = Delivery::factory()->create();
        $driver = $this->userWithRole(RoleName::Driver);

        $response = $this->actingAsRole($driver, RoleName::Driver)
            ->get(route('driver.jobs.show', $delivery));

        $response->assertOk();
    }

    public function test_a_driver_cannot_view_another_drivers_taken_job_on_web(): void
    {
        $owner = $this->userWithRole(RoleName::Driver);
        $delivery = Delivery::factory()->taken()->create(['driver_id' => $owner->id]);
        $other = $this->userWithRole(RoleName::Driver);

        $response = $this->actingAsRole($other, RoleName::Driver)
            ->get(route('driver.jobs.show', $delivery));

        $response->assertForbidden();
    }

    public function test_a_driver_cannot_view_another_drivers_taken_job_on_api(): void
    {
        $owner = $this->userWithRole(RoleName::Driver);
        $delivery = Delivery::factory()->taken()->create(['driver_id' => $owner->id]);
        $other = $this->userWithRole(RoleName::Driver);

        $token = app(RoleService::class)->issueApiToken($other, RoleName::Driver)->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.driver.jobs.show', $delivery));

        $response->assertForbidden();
    }

    public function test_a_driver_can_view_their_own_taken_job(): void
    {
        $driver = $this->userWithRole(RoleName::Driver);
        $delivery = Delivery::factory()->taken()->create(['driver_id' => $driver->id]);

        $response = $this->actingAsRole($driver, RoleName::Driver)
            ->get(route('driver.jobs.show', $delivery));

        $response->assertOk();
    }

    public function test_a_non_admin_cannot_reach_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.promos.index'))->assertForbidden();
    }
}
