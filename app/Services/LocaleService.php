<?php

namespace App\Services;

use App\Enums\Locale;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class LocaleService
{
    /**
     * Persist the chosen locale: the cookie always (so a guest's choice
     * survives), and `users.locale` too when authenticated — never trusts
     * any value beyond the requesting user's own row (§ never trust client
     * for persisted state beyond the user's own data).
     */
    public function update(Request $request, Locale $locale): Cookie
    {
        $request->user()?->update(['locale' => $locale->value]);

        return cookie('locale', $locale->value, 60 * 24 * 365);
    }
}
