<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import LanguageTabs from '@/components/LanguageTabs.vue';
import { edit } from '@/routes/appearance';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tampilan',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Tampilan" />

    <h1 class="sr-only">Tampilan</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('nav.toggleLanguage')"
            :description="t('nav.languagePreferenceDescription')"
        />
        <LanguageTabs />
    </div>
</template>
