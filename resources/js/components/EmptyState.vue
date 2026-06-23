<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { Inbox } from '@lucide/vue';
import { Button } from '@/components/ui/button';

type Props = {
    title: string;
    description?: string;
    icon?: LucideIcon;
    actionLabel?: string;
};

withDefaults(defineProps<Props>(), {
    icon: () => Inbox,
});

defineEmits<{
    action: [];
}>();
</script>

<template>
    <div
        class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border px-6 py-12 text-center"
    >
        <component :is="icon" class="size-10 text-muted-foreground" />
        <div class="space-y-1">
            <p class="font-medium text-foreground">{{ title }}</p>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <Button v-if="actionLabel" size="sm" @click="$emit('action')">
            {{ actionLabel }}
        </Button>
    </div>
</template>
