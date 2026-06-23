<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    /**
     * Role-aware dashboard shell (TDD §13 Day 1 — balance placeholder per
     * role; real per-role functionality lands in later sprints).
     */
    public function index(Request $request): Response
    {
        $view = $this->dashboard->buildView($request);

        return Inertia::render($view['component'], $view['props']);
    }
}
