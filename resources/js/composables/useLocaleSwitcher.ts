import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { update as updateLocale } from '@/routes/locale';

export type AppLocale = 'id' | 'en';

export function useLocaleSwitcher() {
    const { locale } = useI18n();

    function setLocale(value: AppLocale) {
        if (locale.value === value) {
            return;
        }

        // Flip the visible language immediately — the request below only
        // persists the choice (cookie + users.locale), it never gates the
        // UI switch itself.
        locale.value = value;

        router.post(
            updateLocale.url(),
            { locale: value },
            { preserveState: true, preserveScroll: true },
        );
    }

    return { locale, setLocale };
}
