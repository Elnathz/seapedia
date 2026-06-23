<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { home } from '@/routes';
import { index as reviewsIndex } from '@/routes/reviews';

const year = new Date().getFullYear();
const { t } = useI18n();
</script>

<template>
    <footer class="border-t border-border">
        <div
            class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-muted-foreground sm:flex-row sm:px-6"
        >
            <p>{{ t('footer.rights', { year }) }}</p>
            <nav class="flex items-center gap-4" aria-label="Footer">
                <Link :href="home()" class="hover:text-foreground">{{
                    t('footer.home')
                }}</Link>
                <Link
                    :href="reviewsIndex.url()"
                    class="hover:text-foreground"
                    >{{ t('footer.reviews') }}</Link
                >
            </nav>
        </div>
    </footer>
</template>
