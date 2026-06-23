<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve the active locale — authenticated user's `locale` column, else
     * the guest's `locale` cookie, else the app default — and apply it
     * server-side before anything (validation, Inertia share) reads it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $value = $request->user()?->locale ?? $request->cookie('locale');

        $locale = Locale::tryFrom((string) $value) ?? Locale::tryFrom(config('app.locale'));

        App::setLocale($locale?->value ?? Locale::Indonesian->value);

        return $next($request);
    }
}
