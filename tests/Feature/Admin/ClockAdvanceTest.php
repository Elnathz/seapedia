<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\ClockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ClockAdvanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_advances_the_simulated_day_by_one_day(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $before = app(ClockService::class)->now();

        $response = $this->actingAs($admin)->post(route('admin.clock.advance'));

        $response->assertRedirect();
        $after = app(ClockService::class)->now();
        $this->assertSame($before->addDay()->toDateString(), $after->toDateString());
    }

    public function test_a_non_admin_cannot_advance_the_clock(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->post(route('admin.clock.advance'));

        $response->assertForbidden();
    }

    public function test_advance_initializes_from_real_time_when_unset(): void
    {
        Setting::query()->where('key', 'simulated_now')->update(['value' => null]);

        $next = app(ClockService::class)->advance(1);

        $this->assertSame(now()->addDay()->toDateString(), $next->toDateString());
    }

    public function test_the_artisan_command_advances_the_clock(): void
    {
        $before = app(ClockService::class)->now();

        Artisan::call('seapedia:advance-day');

        $after = app(ClockService::class)->now();
        $this->assertSame($before->addDay()->toDateString(), $after->toDateString());
    }

    public function test_admin_advances_the_clock_via_the_api(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('admin')->plainTextToken;
        $before = app(ClockService::class)->now();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.admin.clock.advance'));

        $response->assertOk();
        $response->assertJsonPath('simulated_now', fn ($value) => $value !== null);
        $after = app(ClockService::class)->now();
        $this->assertSame($before->addDay()->toDateString(), $after->toDateString());
    }

    public function test_a_non_admin_cannot_advance_the_clock_via_the_api(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('session')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.admin.clock.advance'));

        $response->assertForbidden();
    }
}
