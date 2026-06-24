<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteJobRequest;
use App\Http\Requests\TakeJobRequest;
use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DriverJobController extends Controller
{
    public function __construct(private readonly DeliveryService $deliveries) {}

    /**
     * Jobs no driver has claimed yet.
     */
    public function index(): Response
    {
        return Inertia::render('driver/jobs/Index', [
            'jobs' => $this->deliveries->availableJobs(),
        ]);
    }

    public function show(Delivery $delivery): Response
    {
        $delivery->load(['order.store:id,name', 'order.items', 'driver:id,name']);

        return Inertia::render('driver/jobs/Show', ['job' => $delivery]);
    }

    /**
     * Claim an available job. A 409 (someone else just took it) is caught
     * here and turned into a quiet toast + a fresh job list, instead of an
     * error page (§ Sprint 5 plan UX direction).
     */
    public function take(TakeJobRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('take', $delivery);

        try {
            $this->deliveries->take($delivery, $request->user());
        } catch (ConflictHttpException) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Sudah diambil oleh kurir lain.')]);

            return to_route('driver.jobs.index');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Job taken.')]);

        return to_route('driver.jobs.show', $delivery);
    }

    public function complete(CompleteJobRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('complete', $delivery);

        $this->deliveries->complete($delivery, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Delivery completed.')]);

        return to_route('driver.jobs.show', $delivery);
    }
}
