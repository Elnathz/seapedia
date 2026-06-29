<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Store;
use App\Models\Wallet;
use App\Services\RoleService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $activeRole = app(RoleService::class)->resolveActiveRole($request)?->value;

        $props = [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ];

        // Load role-specific data
        if ($activeRole === 'buyer') {
            $wallet = Wallet::where('user_id', $user->id)->first();
            $props['wallet'] = $wallet ? ['balance' => $wallet->balance] : null;
            $props['addresses'] = $user->addresses()
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->get();
        }

        if ($activeRole === 'seller') {
            $store = Store::where('user_id', $user->id)
                ->withCount('products')
                ->first();
            $props['store'] = $store ? [
                'name' => $store->name,
                'products_count' => $store->products_count,
            ] : null;
        }

        if ($activeRole === 'driver') {
            $stats = DB::table('deliveries')
                ->where('driver_id', $user->id)
                ->whereNotNull('completed_at')
                ->selectRaw('COUNT(*) as completed_jobs, COALESCE(SUM(earning_amount), 0) as total_earnings')
                ->first();
            $props['driver_stats'] = $stats ? [
                'completed_jobs' => (int) $stats->completed_jobs,
                'total_earnings' => (int) $stats->total_earnings,
            ] : ['completed_jobs' => 0, 'total_earnings' => 0];
        }

        return Inertia::render('settings/Profile', $props);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
