<script setup lang="ts">
import { Languages } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { useLocaleSwitcher } from '@/composables/useLocaleSwitcher';

const { locale, setLocale } = useLocaleSwitcher();
const { t } = useI18n();

function toggle() {
    setLocale(locale.value === 'id' ? 'en' : 'id');
}
</script>

<template>
    <Button
        variant="ghost"
        size="sm"
        :aria-label="t('nav.toggleLanguage')"
        @click="toggle"
    >
        <Languages class="size-4" />
        <span class="text-xs font-medium uppercase">{{ locale }}</span>
    </Button>
</template>
