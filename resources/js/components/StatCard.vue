<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Props = {
    label: string;
    value: string | number;
    icon?: LucideIcon;
};

defineProps<Props>();
</script>

<template>
    <Card>
        <CardHeader
            class="flex flex-row items-center justify-between gap-2 space-y-0 pb-2"
        >
            <CardTitle class="text-sm font-medium text-muted-foreground">
                {{ label }}
            </CardTitle>
            <component
                :is="icon"
                v-if="icon"
                class="size-4 text-muted-foreground"
            />
        </CardHeader>
        <CardContent>
            <p class="text-2xl font-semibold tabular-nums">{{ value }}</p>
        </CardContent>
    </Card>
</template>
