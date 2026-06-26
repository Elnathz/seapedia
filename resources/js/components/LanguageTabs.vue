<script setup lang="ts">
import { useLocaleSwitcher } from '@/composables/useLocaleSwitcher';

const { locale, setLocale } = useLocaleSwitcher();

const tabs = [
    { value: 'id', label: 'Indonesia' },
    { value: 'en', label: 'English' },
] as const;
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-muted p-1">
        <button
            v-for="{ value, label } in tabs"
            :key="value"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 text-sm transition-colors',
                locale === value
                    ? 'bg-background shadow-xs text-foreground'
                    : 'text-muted-foreground hover:bg-muted/80 hover:text-foreground',
            ]"
            @click="setLocale(value)"
        >
            {{ label }}
        </button>
    </div>
</template>
