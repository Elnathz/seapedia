<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\TogglePromoActiveRequest;
use App\Models\Promo;
use App\Services\DiscountService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PromoController extends Controller
{
    public function __construct(private readonly DiscountService $discounts) {}

    public function index(): Response
    {
        $promos = Promo::query()->latest('id')->paginate(10);

        $promos->setCollection(
            $promos->getCollection()->map(fn (Promo $promo) => $this->present($promo)),
        );

        return Inertia::render('admin/promos/Index', ['promos' => $promos]);
    }

    public function show(Promo $promo): Response
    {
        return Inertia::render('admin/promos/Show', ['promo' => $this->present($promo)]);
    }

    public function store(StorePromoRequest $request): RedirectResponse
    {
        $promo = $this->discounts->createPromo($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Promo :code created.', ['code' => $promo->code]),
        ]);

        return to_route('admin.promos.index');
    }

    public function toggleActive(TogglePromoActiveRequest $request, Promo $promo): RedirectResponse
    {
        $this->discounts->togglePromoActive($promo);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Promo $promo): array
    {
        return [
            'id' => $promo->id,
            'code' => $promo->code,
            'type' => $promo->type->value,
            'value' => $promo->value,
            'max_discount' => $promo->max_discount,
            'min_spend' => $promo->min_spend,
            'expiry_date' => $promo->expiry_date->toDateTimeString(),
            'is_active' => $promo->is_active,
            'status' => $this->discounts->statusFor($promo),
        ];
    }
}
