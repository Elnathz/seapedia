<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\ToggleVoucherActiveRequest;
use App\Models\Voucher;
use App\Services\DiscountService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VoucherController extends Controller
{
    public function __construct(private readonly DiscountService $discounts) {}

    public function index(): Response
    {
        $vouchers = Voucher::query()->latest('id')->paginate(10);

        $vouchers->setCollection(
            $vouchers->getCollection()->map(fn (Voucher $voucher) => $this->present($voucher)),
        );

        return Inertia::render('admin/vouchers/Index', ['vouchers' => $vouchers]);
    }

    public function show(Voucher $voucher): Response
    {
        return Inertia::render('admin/vouchers/Show', ['voucher' => $this->present($voucher)]);
    }

    public function store(StoreVoucherRequest $request): RedirectResponse
    {
        $voucher = $this->discounts->createVoucher($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Voucher :code created.', ['code' => $voucher->code]),
        ]);

        return to_route('admin.vouchers.index');
    }

    public function toggleActive(ToggleVoucherActiveRequest $request, Voucher $voucher): RedirectResponse
    {
        $this->discounts->toggleVoucherActive($voucher);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Voucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'type' => $voucher->type->value,
            'value' => $voucher->value,
            'max_discount' => $voucher->max_discount,
            'min_spend' => $voucher->min_spend,
            'expiry_date' => $voucher->expiry_date->toDateTimeString(),
            'usage_limit' => $voucher->usage_limit,
            'used_count' => $voucher->used_count,
            'remaining_usage' => $voucher->remainingUsage(),
            'is_active' => $voucher->is_active,
            'status' => $this->discounts->statusFor($voucher),
        ];
    }
}
