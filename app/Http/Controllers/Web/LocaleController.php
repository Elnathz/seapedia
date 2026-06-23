<?php

namespace App\Http\Controllers\Web;

use App\Enums\Locale;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLocaleRequest;
use App\Services\LocaleService;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __construct(private readonly LocaleService $locales) {}

    /**
     * Switch the UI language — works for guests (cookie) and authenticated
     * users (cookie + `users.locale`). The client already flips the visible
     * language instantly via vue-i18n; this just persists the choice.
     */
    public function update(UpdateLocaleRequest $request): RedirectResponse
    {
        $cookie = $this->locales->update($request, Locale::from($request->validated('locale')));

        return back()->withCookie($cookie);
    }
}
